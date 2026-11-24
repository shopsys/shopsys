<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Processing;

use Itools\SvgSecurityCheck\SvgSecurityCheck;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException;

class ImageProcessor
{
    public const string EXTENSION_JPEG = 'jpeg';
    public const string EXTENSION_JPG = 'jpg';
    public const string EXTENSION_PNG = 'png';
    public const string EXTENSION_GIF = 'gif';
    public const string EXTENSION_SVG = 'svg';
    public const array SUPPORTED_EXTENSIONS = [self::EXTENSION_JPG, self::EXTENSION_JPEG, self::EXTENSION_GIF, self::EXTENSION_PNG];

    protected const int MAX_RASTER_PIXELS = 24_000_000;

    /**
     * @var string[]
     */
    protected array $supportedImageExtensions;

    public function __construct(
        protected readonly FilesystemOperator $filesystem,
    ) {
        $this->supportedImageExtensions = [
            self::EXTENSION_JPEG,
            self::EXTENSION_JPG,
            self::EXTENSION_GIF,
            self::EXTENSION_PNG,
            self::EXTENSION_SVG,
        ];
    }

    protected function getExtensionThrowExceptionIfNotSupported(string $filepath): string
    {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        if (!in_array($extension, $this->supportedImageExtensions, true)) {
            throw new FileIsNotSupportedImageException($filepath);
        }

        return $extension;
    }

    public function getEncodedImageUri(string $filepath): string
    {
        $extension = $this->getExtensionThrowExceptionIfNotSupported($filepath);

        try {
            $mimeType = $this->filesystem->mimeType($filepath);
        } catch (FilesystemException) {
            $mimeType = 'image/png';
        }

        $mediaType = strtolower(trim(explode(';', $mimeType, 2)[0]));

        if ($mediaType === 'image/svg+xml' && $extension !== self::EXTENSION_SVG) {
            throw new FileIsNotSupportedImageException($filepath);
        }

        return sprintf(
            'data:%s;base64,%s',
            $mimeType,
            base64_encode($this->readImage($filepath, $extension)),
        );
    }

    public function convertToShopFormatAndGetNewFilename(string $filepath): string
    {
        $filename = pathinfo($filepath, PATHINFO_FILENAME);
        $extension = $this->getExtensionThrowExceptionIfNotSupported($filepath);
        $newFilepath = pathinfo($filepath, PATHINFO_DIRNAME) . '/' . $filename . '.';

        $newFilepath .= $extension;

        try {
            $file = $this->readImage($filepath, $extension);
            $this->filesystem->delete($filepath);
            $this->filesystem->write($newFilepath, $file);

            return $filename . '.' . $extension;
        } catch (FilesystemException) {
            throw new ImageNotFoundException('File ' . $filepath . ' not found.');
        }
    }

    protected function readImage(string $filepath, string $extension): string
    {
        $contents = $this->filesystem->read($filepath);

        if (!self::isValidImageContent($contents, $extension)) {
            throw new FileIsNotSupportedImageException($filepath);
        }

        return $contents;
    }

    public static function isValidImageContent(string $contents, string $extension): bool
    {
        if (strtolower($extension) === self::EXTENSION_SVG) {
            return SvgSecurityCheck::checkString($contents)->ok;
        }

        $size = @getimagesizefromstring($contents);

        if ($size === false || $size[0] <= 0 || $size[1] <= 0 || $size[0] * $size[1] > static::MAX_RASTER_PIXELS) {
            return false;
        }

        return @imagecreatefromstring($contents) !== false;
    }
}

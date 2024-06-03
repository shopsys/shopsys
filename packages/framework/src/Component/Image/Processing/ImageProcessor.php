<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Processing;

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
    public const array SUPPORTED_EXTENSIONS = [self::EXTENSION_JPG, self::EXTENSION_JPEG, self::EXTENSION_GIF, self::EXTENSION_PNG];
    public const string SUPPORTED_IMAGE_MIME_TYPES = 'image/jpeg|image/gif|image/png';

    /**
     * @var string[]
     */
    protected array $supportedImageExtensions;

    /**
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        protected readonly FilesystemOperator $filesystem,
    ) {
        $this->supportedImageExtensions = [
            self::EXTENSION_JPEG,
            self::EXTENSION_JPG,
            self::EXTENSION_GIF,
            self::EXTENSION_PNG,
        ];
    }

    /**
     * @param string $filepath
     * @return string
     */
    protected function getExtensionThrowExceptionIfNotSupported(string $filepath): string
    {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        if (!in_array($extension, $this->supportedImageExtensions, true)) {
            throw new FileIsNotSupportedImageException($filepath);
        }

        return $extension;
    }

    /**
     * @param string $filepath
     * @return string
     */
    public function getEncodedImageUri(string $filepath): string
    {
        $this->getExtensionThrowExceptionIfNotSupported($filepath);

        try {
            $mimeType = $this->filesystem->mimeType($filepath);
        } catch (FilesystemException) {
            $mimeType = 'image/png';
        }

        return sprintf(
            'data:%s;base64,%s',
            $mimeType,
            base64_encode($this->filesystem->read($filepath)),
        );
    }

    /**
     * @param string $filepath
     * @return string
     */
    public function convertToShopFormatAndGetNewFilename(string $filepath): string
    {
        $filename = pathinfo($filepath, PATHINFO_FILENAME);
        $extension = $this->getExtensionThrowExceptionIfNotSupported($filepath);
        $newFilepath = pathinfo($filepath, PATHINFO_DIRNAME) . '/' . $filename . '.';

        $newFilepath .= $extension;

        try {
            $file = $this->filesystem->read($filepath);
            $this->filesystem->delete($filepath);
            $this->filesystem->write($newFilepath, $file);

            return $filename . '.' . $extension;
        } catch (FilesystemException) {
            throw new ImageNotFoundException('File ' . $filepath . ' not found.');
        }
    }
}

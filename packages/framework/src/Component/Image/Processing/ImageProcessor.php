<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Processing;

use Intervention\Image\Constraint;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
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
     * @param \Intervention\Image\ImageManager $imageManager
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        protected readonly ImageManager $imageManager,
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
     * @return \Intervention\Image\Image
     */
    public function createInterventionImage(string $filepath): Image
    {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        if (!in_array($extension, $this->supportedImageExtensions, true)) {
            throw new FileIsNotSupportedImageException($filepath);
        }

        try {
            if ($this->filesystem->has($filepath)) {
                $file = $this->filesystem->read($filepath);

                return $this->imageManager->make($file);
            }

            throw new ImageNotFoundException('File ' . $filepath . ' not found.');
        } catch (NotReadableException $ex) {
            throw new FileIsNotSupportedImageException($filepath, $ex);
        }
    }

    /**
     * @param string $filepath
     * @return string
     */
    public function convertToShopFormatAndGetNewFilename(string $filepath): string
    {
        $filename = pathinfo($filepath, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        $newFilepath = pathinfo($filepath, PATHINFO_DIRNAME) . '/' . $filename . '.';

        if (!in_array($extension, $this->supportedImageExtensions, true)) {
            throw new FileIsNotSupportedImageException($filepath);
        }

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

    /**
     * @param \Intervention\Image\Image $image
     * @param int|null $width
     * @param int|null $height
     * @param bool $crop
     * @return \Intervention\Image\Image
     */
    public function resize(Image $image, ?int $width, ?int $height, bool $crop = false): Image
    {
        if ($crop) {
            $image->fit($width, $height, function (Constraint $constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        } else {
            $image->resize($width, $height, function (Constraint $constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        return $image;
    }
}

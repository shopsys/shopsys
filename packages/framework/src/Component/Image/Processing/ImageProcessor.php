<?php

namespace Shopsys\FrameworkBundle\Component\Image\Processing;

use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageAdditionalSizeConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig;

class ImageProcessor
{
    public const EXTENSION_JPEG = 'jpeg';
    public const EXTENSION_JPG = 'jpg';
    public const EXTENSION_PNG = 'png';
    public const EXTENSION_GIF = 'gif';
    public const SUPPORTED_EXTENSIONS = [self::EXTENSION_JPG, self::EXTENSION_JPEG, self::EXTENSION_GIF, self::EXTENSION_PNG];
    public const SUPPORTED_IMAGE_MIME_TYPES = 'image/jpeg|image/gif|image/png';

    /**
     * @var string[]
     */
    protected $supportedImageExtensions;

    /**
     * @var \Intervention\Image\ImageManager
     */
    protected $imageManager;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @param \Intervention\Image\ImageManager $imageManager
     * @param \League\Flysystem\FilesystemInterface $filesystem
     */
    public function __construct(
        ImageManager $imageManager,
        FilesystemInterface $filesystem
    ) {
        $this->imageManager = $imageManager;
        $this->filesystem = $filesystem;

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
    public function createInterventionImage($filepath)
    {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        if (!in_array($extension, $this->supportedImageExtensions, true)) {
            throw new \Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException($filepath);
        }
        try {
            if ($this->filesystem->has($filepath)) {
                $file = $this->filesystem->read($filepath);

                return $this->imageManager->make($file);
            } else {
                throw new \Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException('File ' . $filepath . ' not found.');
            }
        } catch (\Intervention\Image\Exception\NotReadableException $ex) {
            throw new \Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException($filepath, $ex);
        }
    }

    /**
     * @param string $filepath
     * @return string
     */
    public function convertToShopFormatAndGetNewFilename($filepath)
    {
        $filename = pathinfo($filepath, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        $newFilepath = pathinfo($filepath, PATHINFO_DIRNAME) . '/' . $filename . '.';

        if ($extension === self::EXTENSION_PNG) {
            $extension = self::EXTENSION_PNG;
        } elseif (in_array($extension, $this->supportedImageExtensions, true)) {
            $extension = self::EXTENSION_JPG;
        } else {
            throw new \Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException($filepath);
        }
        $newFilepath .= $extension;

        $image = $this->createInterventionImage($filepath);
        $data = $image->encode($extension);

        $this->filesystem->delete($filepath);
        $this->filesystem->write($newFilepath, $data);

        return $filename . '.' . $extension;
    }

    /**
     * @param \Intervention\Image\Image $image
     * @param int|null $width
     * @param int|null $height
     * @param bool $crop
     * @return \Intervention\Image\Image
     */
    public function resize(Image $image, $width, $height, $crop = false)
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

    /**
     * @param \Intervention\Image\Image $image
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig $sizeConfig
     */
    public function resizeBySizeConfig(Image $image, ImageSizeConfig $sizeConfig)
    {
        $this->resize($image, $sizeConfig->getWidth(), $sizeConfig->getHeight(), $sizeConfig->getCrop());
    }

    /**
     * @param \Intervention\Image\Image $image
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig $sizeConfig
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageAdditionalSizeConfig $additionalSizeConfig
     */
    public function resizeByAdditionalSizeConfig(Image $image, ImageSizeConfig $sizeConfig, ImageAdditionalSizeConfig $additionalSizeConfig)
    {
        $this->resize($image, $additionalSizeConfig->getWidth(), $additionalSizeConfig->getHeight(), $sizeConfig->getCrop());
    }

    /**
     * @return string[]
     */
    public function getSupportedImageExtensions()
    {
        return $this->supportedImageExtensions;
    }
}

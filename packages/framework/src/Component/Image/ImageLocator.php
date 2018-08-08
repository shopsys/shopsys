<?php

namespace Shopsys\FrameworkBundle\Component\Image;

use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;

class ImageLocator
{
    /**
     * @var string
     */
    private $imageDir;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig
     */
    private $imageConfig;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    public function __construct($imageDir, ImageConfig $imageConfig, FilesystemInterface $filesystem)
    {
        $this->imageDir = $imageDir;
        $this->imageConfig = $imageConfig;
        $this->filesystem = $filesystem;
    }

    public function getRelativeImageFilepath(Image $image, ?string $sizeName): string
    {
        $path = $this->getRelativeImagePath($image->getEntityName(), $image->getType(), $sizeName);

        return $path . $image->getFilename();
    }

    public function getAbsoluteImageFilepath(Image $image, ?string $sizeName): string
    {
        $relativePath = $this->getRelativeImageFilepath($image, $sizeName);

        return $this->imageDir . $relativePath;
    }

    public function imageExists(Image $image): bool
    {
        $imageFilepath = $this->getAbsoluteImageFilepath($image, ImageConfig::ORIGINAL_SIZE_NAME);

        return $this->filesystem->has($imageFilepath);
    }

    public function getRelativeImagePath(string $entityName, ?string $type, ?string $sizeName): string
    {
        $this->imageConfig->assertImageSizeConfigByEntityNameExists($entityName, $type, $sizeName);
        $pathParts = [$entityName];

        if ($type !== null) {
            $pathParts[] = $type;
        }
        if ($sizeName === null) {
            $pathParts[] = ImageConfig::DEFAULT_SIZE_NAME;
        } else {
            $pathParts[] = $sizeName;
        }

        return implode('/', $pathParts) . '/';
    }
}

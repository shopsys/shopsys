<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;

class ImageLocator
{
    protected string $imageDir;

    /**
     * @param mixed $imageDir
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        $imageDir,
        protected readonly ImageConfig $imageConfig,
        protected readonly FilesystemOperator $filesystem,
    ) {
        $this->imageDir = $imageDir;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $image
     * @return string
     */
    public function getRelativeImageFilepath(Image $image): string
    {
        $path = $this->getRelativeImagePath($image->getEntityName(), $image->getType());

        return $path . $image->getFilename();
    }

    /**
     * @param int $id
     * @param string $extension
     * @param string $entityName
     * @param string|null $type
     * @return string
     */
    public function getRelativeImageFilepathFromAttributes(
        int $id,
        string $extension,
        string $entityName,
        ?string $type,
    ): string {
        $path = $this->getRelativeImagePath($entityName, $type);

        $filename = $id . '.' . $extension;

        return $path . $filename;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $image
     * @return string
     */
    public function getAbsoluteImageFilepath(Image $image): string
    {
        $relativePath = $this->getRelativeImageFilepath($image);

        return $this->imageDir . $relativePath;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $image
     * @return bool
     */
    public function imageExists(Image $image)
    {
        $imageFilepath = $this->getAbsoluteImageFilepath($image);

        return $this->filesystem->has($imageFilepath);
    }

    /**
     * @param string $entityName
     * @param string|null $type
     * @return string
     */
    public function getRelativeImagePath(string $entityName, ?string $type): string
    {
        $this->imageConfig->assertImageConfigByEntityNameExists($entityName, $type);
        $pathParts = [$entityName];

        if ($type !== null) {
            $pathParts[] = $type;
        }

        $pathParts[] = ImageConfig::ORIGINAL_SIZE_NAME;

        return implode('/', $pathParts) . '/';
    }
}

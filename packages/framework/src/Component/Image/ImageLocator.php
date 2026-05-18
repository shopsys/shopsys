<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;

class ImageLocator
{
    protected string $imageDir;

    public function __construct(
        mixed $imageDir,
        protected readonly ImageConfig $imageConfig,
        protected readonly FilesystemOperator $filesystem,
    ) {
        $this->imageDir = $imageDir;
    }

    public function getRelativeImageFilepath(Image $image): string
    {
        $path = $this->getRelativeImagePath($image->getEntityName(), $image->getType());

        return $path . '/' . $image->getFilename();
    }

    public function getRelativeImageFilepathFromAttributes(
        int $id,
        string $extension,
        string $entityName,
        ?string $type,
    ): string {
        $path = $this->getRelativeImagePath($entityName, $type);

        $filename = $id . '.' . $extension;

        return $path . '/' . $filename;
    }

    public function getAbsoluteImageFilepath(Image $image): string
    {
        $relativePath = $this->getRelativeImageFilepath($image);

        return $this->imageDir . $relativePath;
    }

    public function getAbsoluteImageFilepathFromAttributes(
        int $id,
        string $extension,
        string $entityName,
        ?string $type,
    ): string {
        return $this->imageDir . $this->getRelativeImageFilepathFromAttributes($id, $extension, $entityName, $type);
    }

    public function imageExists(Image $image): bool
    {
        $imageFilepath = $this->getAbsoluteImageFilepath($image);

        return $this->filesystem->has($imageFilepath);
    }

    public function getRelativeImagePath(string $entityName, ?string $type): string
    {
        $this->imageConfig->assertImageConfigByEntityNameExists($entityName, $type);
        $pathParts = [$entityName];

        if ($type !== null) {
            $pathParts[] = $type;
        }

        return implode('/', $pathParts);
    }

    public function getRelativeImageFilepathWithSlug(Image $image, ?string $friendlyUrlSlug): string
    {
        $path = $this->getRelativeImagePath($image->getEntityName(), $image->getType());

        return $path . '/' . $image->getSeoFilename($friendlyUrlSlug);
    }
}

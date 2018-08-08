<?php

namespace Shopsys\FrameworkBundle\Component\Image;

use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;

class DirectoryStructureCreator
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig
     */
    private $imageConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageLocator
     */
    private $imageLocator;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    /**
     * @var string
     */
    private $imageDir;

    /**
     * @var string
     */
    private $domainImageDir;

    public function __construct(
        string $imageDir,
        string $domainImageDir,
        ImageConfig $imageConfig,
        ImageLocator $imageLocator,
        FilesystemInterface $filesystem
    ) {
        $this->imageDir = $imageDir;
        $this->domainImageDir = $domainImageDir;
        $this->imageConfig = $imageConfig;
        $this->imageLocator = $imageLocator;
        $this->filesystem = $filesystem;
    }

    public function makeImageDirectories(): void
    {
        $imageEntityConfigs = $this->imageConfig->getAllImageEntityConfigsByClass();
        $directories = [];
        foreach ($imageEntityConfigs as $imageEntityConfig) {
            $sizeConfigs = $imageEntityConfig->getSizeConfigs();
            $sizesDirectories = $this->getTargetDirectoriesFromSizeConfigs(
                $imageEntityConfig->getEntityName(),
                null,
                $sizeConfigs
            );
            $directories = array_merge($directories, $sizesDirectories);

            foreach ($imageEntityConfig->getTypes() as $type) {
                $typeSizes = $imageEntityConfig->getSizeConfigsByType($type);
                $typeSizesDirectories = $this->getTargetDirectoriesFromSizeConfigs(
                    $imageEntityConfig->getEntityName(),
                    $type,
                    $typeSizes
                );
                $directories = array_merge($directories, $typeSizesDirectories);
            }
        }

        $directories[] = $this->domainImageDir;

        foreach ($directories as $directory) {
            $this->filesystem->createDir($directory);
        }
    }

    /**
     * @param string|null $type
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[] $sizeConfigs
     * @return string[]
     */
    private function getTargetDirectoriesFromSizeConfigs(string $entityName, ?string $type, array $sizeConfigs): array
    {
        $directories = [];
        foreach ($sizeConfigs as $sizeConfig) {
            $relativePath = $this->imageLocator->getRelativeImagePath($entityName, $type, $sizeConfig->getName());
            $directories[] = $this->imageDir . $relativePath;
        }

        return $directories;
    }
}

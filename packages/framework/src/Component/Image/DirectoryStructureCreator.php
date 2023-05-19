<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use League\Flysystem\Config;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;

class DirectoryStructureCreator
{
    protected string $imageDir;

    protected string $domainImageDir;

    /**
     * @param string $imageDir
     * @param string $domainImageDir
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageLocator $imageLocator
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        $imageDir,
        $domainImageDir,
        protected readonly ImageConfig $imageConfig,
        protected readonly ImageLocator $imageLocator,
        protected readonly FilesystemOperator $filesystem,
    ) {
        $this->imageDir = $imageDir;
        $this->domainImageDir = $domainImageDir;
    }

    public function makeImageDirectories()
    {
        $imageEntityConfigs = $this->imageConfig->getAllImageEntityConfigsByClass();
        $directories = [];

        foreach ($imageEntityConfigs as $imageEntityConfig) {
            $sizeConfigs = $imageEntityConfig->getSizeConfigs();
            $sizesDirectories = $this->getTargetDirectoriesFromSizeConfigs(
                $imageEntityConfig->getEntityName(),
                null,
                $sizeConfigs,
            );
            $directories = array_merge($directories, $sizesDirectories);

            foreach ($imageEntityConfig->getTypes() as $type) {
                $typeSizes = $imageEntityConfig->getSizeConfigsByType($type);
                $typeSizesDirectories = $this->getTargetDirectoriesFromSizeConfigs(
                    $imageEntityConfig->getEntityName(),
                    $type,
                    $typeSizes,
                );
                $directories = array_merge($directories, $typeSizesDirectories);
            }
        }

        $directories[] = $this->domainImageDir;

        foreach ($directories as $directory) {
            $this->filesystem->createDirectory($directory, [Config::OPTION_VISIBILITY => Visibility::PUBLIC]);
        }
    }

    /**
     * @param string $entityName
     * @param string|null $type
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[] $sizeConfigs
     * @return string[]
     */
    protected function getTargetDirectoriesFromSizeConfigs($entityName, $type, array $sizeConfigs)
    {
        $directories = [];

        foreach ($sizeConfigs as $sizeConfig) {
            $relativePath = $this->imageLocator->getRelativeImagePath($entityName, $type, $sizeConfig->getName());
            $directories[] = $this->imageDir . $relativePath;
        }

        return $directories;
    }
}

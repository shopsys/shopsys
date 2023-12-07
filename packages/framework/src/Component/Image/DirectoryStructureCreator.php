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

    public function makeImageDirectories(): void
    {
        $imageEntityConfigs = $this->imageConfig->getAllImageEntityConfigsByClass();
        $directories = [];

        foreach ($imageEntityConfigs as $imageEntityConfig) {
            $types = $imageEntityConfig->getTypes();

            if (count($types) === 0) {
                $directories[] = $this->getTargetDirectoryByType(
                    $imageEntityConfig->getEntityName(),
                    null,
                );
            }

            foreach ($types as $type) {
                $directories[] = $this->getTargetDirectoryByType(
                    $imageEntityConfig->getEntityName(),
                    $type,
                );
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
     * @return string
     */
    protected function getTargetDirectoryByType(string $entityName, ?string $type): string
    {
        return $this->imageDir . $this->imageLocator->getRelativeImagePath($entityName, $type);
    }
}

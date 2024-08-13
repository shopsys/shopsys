<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use League\Flysystem\Config;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileConfig;

class DirectoryStructureCreator
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileConfig $customerUploadedFileConfig
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileLocator $customerUploadedFileLocator
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        protected readonly CustomerUploadedFileConfig $customerUploadedFileConfig,
        protected readonly CustomerUploadedFileLocator $customerUploadedFileLocator,
        protected FilesystemOperator $filesystem,
    ) {
    }

    public function makeCustomerUploadedFileDirectories(): void
    {
        $customerUploadedFileEntityConfigs = $this->customerUploadedFileConfig->getAllUploadedFileEntityConfigs();
        $directories = [];

        foreach ($customerUploadedFileEntityConfigs as $customerUploadedFileEntityConfig) {
            $directories[] = $this->customerUploadedFileLocator->getAbsoluteFilePath(
                $customerUploadedFileEntityConfig->getEntityName(),
            );
        }

        foreach ($directories as $directory) {
            $this->filesystem->createDirectory($directory, [Config::OPTION_VISIBILITY => Visibility::PRIVATE]);
        }
    }
}

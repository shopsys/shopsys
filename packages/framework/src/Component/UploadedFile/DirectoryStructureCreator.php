<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use League\Flysystem\Config;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;

class DirectoryStructureCreator
{
    protected UploadedFileConfig $uploadedFileConfig;

    protected UploadedFileLocator $uploadedFileLocator;

    protected FilesystemOperator $filesysytem;

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig $uploadedFileConfig
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator $uploadedFileLocator
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        UploadedFileConfig $uploadedFileConfig,
        UploadedFileLocator $uploadedFileLocator,
        FilesystemOperator $filesystem
    ) {
        $this->uploadedFileConfig = $uploadedFileConfig;
        $this->uploadedFileLocator = $uploadedFileLocator;
        $this->filesysytem = $filesystem;
    }

    public function makeUploadedFileDirectories()
    {
        $uploadedFileEntityConfigs = $this->uploadedFileConfig->getAllUploadedFileEntityConfigs();
        $directories = [];

        foreach ($uploadedFileEntityConfigs as $uploadedFileEntityConfig) {
            $directories[] = $this->uploadedFileLocator->getAbsoluteFilePath(
                $uploadedFileEntityConfig->getEntityName()
            );
        }

        foreach ($directories as $directory) {
            $this->filesysytem->createDirectory($directory, [Config::OPTION_VISIBILITY => Visibility::PUBLIC]);
        }
    }
}

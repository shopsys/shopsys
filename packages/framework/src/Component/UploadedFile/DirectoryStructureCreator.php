<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use League\Flysystem\Config;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;

class DirectoryStructureCreator
{
    protected FilesystemOperator $filesysytem;

    public function __construct(
        protected readonly UploadedFileConfig $uploadedFileConfig,
        protected readonly UploadedFileLocator $uploadedFileLocator,
        FilesystemOperator $filesystem,
    ) {
        $this->filesysytem = $filesystem;
    }

    public function makeUploadedFileDirectories()
    {
        $uploadedFileEntityConfigs = $this->uploadedFileConfig->getAllUploadedFileEntityConfigs();
        $directories = [];

        foreach ($uploadedFileEntityConfigs as $uploadedFileEntityConfig) {
            $directories[] = $this->uploadedFileLocator->getAbsoluteFilePath(
                $uploadedFileEntityConfig->getEntityName(),
            );
        }

        foreach ($directories as $directory) {
            $this->filesysytem->createDirectory($directory, [Config::OPTION_VISIBILITY => Visibility::PUBLIC]);
        }
    }
}

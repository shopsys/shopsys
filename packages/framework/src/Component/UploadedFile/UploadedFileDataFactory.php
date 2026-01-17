<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;

class UploadedFileDataFactory
{
    public function __construct(protected readonly UploadedFileFacade $uploadedFileFacade)
    {
    }

    public function createInstance(): UploadedFileData
    {
        return new UploadedFileData();
    }

    public function create(): UploadedFileData
    {
        return $this->createInstance();
    }

    public function createByEntity(
        object $entity,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): UploadedFileData {
        $uploadedFileData = $this->createInstance();

        $this->fillByUploadedFiles(
            $uploadedFileData,
            $this->uploadedFileFacade->getUploadedFilesByEntity($entity, $type),
        );

        return $uploadedFileData;
    }

    protected function fillByUploadedFiles(UploadedFileData $uploadedFileData, array $uploadedFiles): void
    {
        $uploadedFileData->orderedFiles = $uploadedFiles;

        foreach ($uploadedFileData->orderedFiles as $file) {
            $uploadedFileData->currentFilenamesIndexedById[$file->getId()] = $file->getName();
            $uploadedFileData->namesIndexedById[$file->getId()] = $file->getTranslatedNames();
        }
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;

class CustomerUploadedFileDataFactory
{
    public function __construct(protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade)
    {
    }

    public function createInstance(): CustomerUploadedFileData
    {
        return new CustomerUploadedFileData();
    }

    public function create(): CustomerUploadedFileData
    {
        return $this->createInstance();
    }

    public function createByEntity(
        object $entity,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): CustomerUploadedFileData {
        $customerUploadedFileData = $this->createInstance();

        $this->fillByCustomerUploadedFiles(
            $customerUploadedFileData,
            $this->customerUploadedFileFacade->getUploadedFilesByEntity($entity, $type),
        );

        return $customerUploadedFileData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[] $customerUploadedFiles
     */
    protected function fillByCustomerUploadedFiles(
        CustomerUploadedFileData $customerUploadedFileData,
        array $customerUploadedFiles,
    ): void {
        $customerUploadedFileData->orderedFiles = $customerUploadedFiles;

        foreach ($customerUploadedFileData->orderedFiles as $file) {
            $customerUploadedFileData->currentFilenamesIndexedById[$file->getId()] = $file->getName();
        }
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileTypeConfig;

class CustomerUploadedFileDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade $customerUploadedFileFacade
     */
    public function __construct(protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileData
     */
    public function createInstance(): CustomerUploadedFileData
    {
        return new CustomerUploadedFileData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileData
     */
    public function create(): CustomerUploadedFileData
    {
        return $this->createInstance();
    }

    /**
     * @param object $entity
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileData
     */
    public function createByEntity(
        object $entity,
        string $type = CustomerUploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): CustomerUploadedFileData {
        $customerUploadedFileData = $this->createInstance();

        $this->fillByCustomerUploadedFiles(
            $customerUploadedFileData,
            $this->customerUploadedFileFacade->getUploadedFilesByEntity($entity, $type),
        );

        return $customerUploadedFileData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileData $customerUploadedFileData
     * @param array $customerUploadedFiles
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

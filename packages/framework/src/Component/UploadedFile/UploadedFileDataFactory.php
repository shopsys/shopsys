<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;

class UploadedFileDataFactory implements UploadedFileDataFactoryInterface
{
    protected UploadedFileFacade $uploadedFileFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(UploadedFileFacade $uploadedFileFacade)
    {
        $this->uploadedFileFacade = $uploadedFileFacade;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData
     */
    public function createInstance(): UploadedFileData
    {
        return new UploadedFileData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData
     */
    public function create(): UploadedFileData
    {
        return $this->createInstance();
    }

    /**
     * @param object $entity
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData
     */
    public function createByEntity(object $entity, string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME): UploadedFileData
    {
        $uploadedFileData = $this->createInstance();

        $this->fillByUploadedFiles(
            $uploadedFileData,
            $this->uploadedFileFacade->getUploadedFilesByEntity($entity, $type)
        );

        return $uploadedFileData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData $uploadedFileData
     * @param array $uploadedFiles
     */
    protected function fillByUploadedFiles(UploadedFileData $uploadedFileData, array $uploadedFiles): void
    {
        $uploadedFileData->orderedFiles = $uploadedFiles;

        foreach ($uploadedFileData->orderedFiles as $file) {
            $uploadedFileData->currentFilenamesIndexedById[$file->getId()] = $file->getName();
        }
    }
}

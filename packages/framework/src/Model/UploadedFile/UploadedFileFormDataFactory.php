<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\UploadedFile;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory;

class UploadedFileFormDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory $uploadedFileDataFactory
     */
    public function __construct(
        protected readonly UploadedFileDataFactory $uploadedFileDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return \Shopsys\FrameworkBundle\Model\UploadedFile\UploadedFileFormData
     */
    public function create(UploadedFile $uploadedFile): UploadedFileFormData
    {
        $uploadedFileData = $this->uploadedFileDataFactory->create();
        $uploadedFileData->orderedFiles = [$uploadedFile];

        $uploadedFileFormData = $this->createInstance();
        $uploadedFileFormData->files = $uploadedFileData;
        $uploadedFileFormData->name = $uploadedFile->getName();
        $uploadedFileFormData->names = $uploadedFile->getTranslatedNames();

        return $uploadedFileFormData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\UploadedFile\UploadedFileFormData
     */
    public function createInstance(): UploadedFileFormData
    {
        return new UploadedFileFormData();
    }
}

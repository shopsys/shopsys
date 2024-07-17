<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\UploadedFile;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class UploadedFileFormDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory $uploadedFileDataFactory
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        protected readonly UploadedFileDataFactory $uploadedFileDataFactory,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly ProductFacade $productFacade,
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

        $entityIds = $this->uploadedFileFacade->getEntityIdsForUploadedFile($uploadedFile);
        $uploadedFileFormData->products = $this->productFacade->getAllByIds($entityIds);

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

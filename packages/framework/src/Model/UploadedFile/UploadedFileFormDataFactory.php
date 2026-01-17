<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\UploadedFile;

use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class UploadedFileFormDataFactory
{
    public function __construct(
        protected readonly UploadedFileDataFactory $uploadedFileDataFactory,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly ProductFacade $productFacade,
    ) {
    }

    public function create(UploadedFile $uploadedFile): UploadedFileFormData
    {
        $uploadedFileData = $this->uploadedFileDataFactory->create();
        $uploadedFileData->orderedFiles = [$uploadedFile];

        $uploadedFileFormData = $this->createInstance();
        $uploadedFileFormData->files = $uploadedFileData;
        $uploadedFileFormData->name = $uploadedFile->getName();
        $uploadedFileFormData->names = $uploadedFile->getTranslatedNames();

        $entityIds = $this->uploadedFileFacade->getEntityIdsForUploadedFile($uploadedFile, Product::class, UploadedFileTypeConfig::DEFAULT_TYPE_NAME);
        $uploadedFileFormData->products = $this->productFacade->getAllByIds($entityIds);

        return $uploadedFileFormData;
    }

    public function createInstance(): UploadedFileFormData
    {
        return new UploadedFileFormData();
    }
}

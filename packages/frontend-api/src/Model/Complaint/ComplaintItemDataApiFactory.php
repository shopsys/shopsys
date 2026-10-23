<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint;

use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileDataFactory;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class ComplaintItemDataApiFactory
{
    public function __construct(
        protected readonly ComplaintItemDataFactory $complaintItemDataFactory,
        protected readonly CustomerUploadedFileDataFactory $customerUploadedFileDataFactory,
        protected readonly FileUpload $fileUpload,
        protected readonly ProductFacade $productFacade,
    ) {
    }

    public function createFromComplaintItemInput(array $itemInputData, ?OrderItem $orderItem = null): ComplaintItemData
    {
        $manualComplaintItemCatnum = $itemInputData['manualComplaintItemCatnum'];
        $product = $orderItem?->getProduct();
        $productName = $orderItem?->getName() ?? $itemInputData['manualComplaintItemName'];
        $catnum = $orderItem?->getCatnum() ?? $manualComplaintItemCatnum;

        if ($product) {
            $productName ??= $product->getName();
            $catnum ??= $product->getCatnum();
        }

        $complaintItemData = $this->complaintItemDataFactory->create();
        $complaintItemData->orderItem = $orderItem;
        $complaintItemData->product = $product ?? $this->findProductByManualCatnum($manualComplaintItemCatnum);
        $complaintItemData->productName = $productName;
        $complaintItemData->catnum = $catnum;
        $complaintItemData->quantity = $itemInputData['quantity'];
        $complaintItemData->description = $itemInputData['description'];

        $complaintItemData->files = $this->customerUploadedFileDataFactory->create();

        foreach ($itemInputData['files'] as $uploadedFile) {
            $complaintItemData->files->uploadedFiles[] = $this->fileUpload->upload($uploadedFile);
            $complaintItemData->files->uploadedFilenames[] = $uploadedFile->getClientOriginalName();
        }

        return $complaintItemData;
    }

    protected function findProductByManualCatnum(?string $manualComplaintItemCatnum): ?Product
    {
        if ($manualComplaintItemCatnum === null) {
            return null;
        }

        return $this->productFacade->findByCatnum($manualComplaintItemCatnum);
    }
}

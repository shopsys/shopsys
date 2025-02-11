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
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemDataFactory $complaintItemDataFactory
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileDataFactory $customerUploadedFileDataFactory
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        protected readonly ComplaintItemDataFactory $complaintItemDataFactory,
        protected readonly CustomerUploadedFileDataFactory $customerUploadedFileDataFactory,
        protected readonly FileUpload $fileUpload,
        protected readonly ProductFacade $productFacade,
    ) {
    }

    /**
     * @param array $itemInputData
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem|null $orderItem
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData
     */
    public function createFromComplaintItemInput(array $itemInputData, ?OrderItem $orderItem = null): ComplaintItemData
    {
        $manualComplaintItemCatnum = $itemInputData['manualComplaintItemCatnum'];
        $product = $orderItem?->getProduct();
        $productName = $orderItem?->getName() ?? $itemInputData['manualComplaintItemName'];
        $catnum = $orderItem?->getCatnum() ?? $manualComplaintItemCatnum;

        if ($product) {
            $productName = $productName ?? $product->getName();
            $catnum = $catnum ?? $product->getCatnum();
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

    /**
     * @param string|null $manualComplaintItemCatnum
     * @return \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    protected function findProductByManualCatnum(?string $manualComplaintItemCatnum): ?Product
    {
        if ($manualComplaintItemCatnum === null) {
            return null;
        }

        return $this->productFacade->findByCatnum($manualComplaintItemCatnum);
    }
}

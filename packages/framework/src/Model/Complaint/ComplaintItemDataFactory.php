<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileDataFactory;

class ComplaintItemDataFactory
{
    public function __construct(
        protected readonly CustomerUploadedFileDataFactory $customerUploadedFileDataFactory,
    ) {
    }

    public function create(): ComplaintItemData
    {
        return $this->createInstance();
    }

    protected function createInstance(): ComplaintItemData
    {
        return new ComplaintItemData();
    }

    public function createFromComplaintItem(ComplaintItem $complaintItem): ComplaintItemData
    {
        $complaintItemData = $this->createInstance();

        $complaintItemData->uuid = $complaintItem->getUuid();
        $complaintItemData->quantity = $complaintItem->getQuantity();
        $complaintItemData->description = $complaintItem->getDescription();
        $complaintItemData->orderItem = $complaintItem->getOrderItem();
        $complaintItemData->files = $this->customerUploadedFileDataFactory->createByEntity($complaintItem);
        $complaintItemData->productName = $complaintItem->getProductName();
        $complaintItemData->catnum = $complaintItem->getCatnum();
        $complaintItemData->product = $complaintItem->getProduct();

        return $complaintItemData;
    }
}

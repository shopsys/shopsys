<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileDataFactory;

class ComplaintItemDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileDataFactory $customerUploadedFileDataFactory
     */
    public function __construct(
        protected readonly CustomerUploadedFileDataFactory $customerUploadedFileDataFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData
     */
    public function create(): ComplaintItemData
    {
        return $this->createInstance();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData
     */
    protected function createInstance(): ComplaintItemData
    {
        return new ComplaintItemData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem $complaintItem
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData
     */
    public function createFromComplaintItem(ComplaintItem $complaintItem): ComplaintItemData
    {
        $complaintItemData = $this->createInstance();

        $complaintItemData->quantity = $complaintItem->getQuantity();
        $complaintItemData->description = $complaintItem->getDescription();
        $complaintItemData->orderItem = $complaintItem->getOrderItem();
        $complaintItemData->files = $this->customerUploadedFileDataFactory->createByEntity($complaintItem);

        return $complaintItemData;
    }
}

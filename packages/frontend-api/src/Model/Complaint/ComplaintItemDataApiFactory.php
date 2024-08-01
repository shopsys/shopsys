<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint;

use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;

class ComplaintItemDataApiFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemDataFactory $complaintItemDataFactory
     */
    public function __construct(protected readonly ComplaintItemDataFactory $complaintItemDataFactory)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem $orderItem
     * @param array $item
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData
     */
    public function createFromComplaintItemInput(OrderItem $orderItem, array $item): ComplaintItemData
    {
        $complaintItemData = $this->complaintItemDataFactory->create();
        $complaintItemData->orderItem = $orderItem;
        $complaintItemData->quantity = $item['quantity'];
        $complaintItemData->description = $item['description'];

        return $complaintItemData;
    }
}

<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Complaint;

use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem;
use Shopsys\FrontendApiBundle\Model\ScalarType\DateTimeType;

trait ComplaintTestTrait
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem $expectedComplaintItem
     * @param array $complaintItem
     */
    protected function assertComplaintItem(ComplaintItem $expectedComplaintItem, array $complaintItem): void
    {
        $complaintItemMessage = sprintf(
            'Hint: check data of complaint item with ID #%d',
            $expectedComplaintItem->getId(),
        );

        $this->assertArrayHasKey('quantity', $complaintItem, $complaintItemMessage);
        $this->assertSame($expectedComplaintItem->getQuantity(), $complaintItem['quantity'], $complaintItemMessage);

        $this->assertArrayHasKey('description', $complaintItem, $complaintItemMessage);
        $this->assertSame($expectedComplaintItem->getDescription(), $complaintItem['description'], $complaintItemMessage);

        $this->assertArrayHasKey('orderItem', $complaintItem, $complaintItemMessage);

        $orderItem = $complaintItem['orderItem'];
        $expectedOrderItem = $expectedComplaintItem->getOrderItem();

        $this->assertArrayHasKey('uuid', $orderItem, $complaintItemMessage);
        $this->assertSame($expectedOrderItem->getUuid(), $orderItem['uuid'], $complaintItemMessage);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Complaint $expectedComplaint
     * @param array $complaint
     */
    protected function assertComplaint(Complaint $expectedComplaint, array $complaint): void
    {
        $complaintMessage = sprintf(
            'Hint: check data and sort of complaint with ID #%d',
            $expectedComplaint->getId(),
        );


        $this->assertArrayHasKey('uuid', $complaint, $complaintMessage);
        $this->assertSame($expectedComplaint->getUuid(), $complaint['uuid'], $complaintMessage);

        $this->assertArrayHasKey('number', $complaint, $complaintMessage);
        $this->assertSame($expectedComplaint->getNumber(), $complaint['number'], $complaintMessage);

        $this->assertArrayHasKey('createdAt', $complaint, $complaintMessage);
        $this->assertSame(DateTimeType::serialize($expectedComplaint->getCreatedAt()), $complaint['createdAt'], $complaintMessage);

        $this->assertArrayHasKey('deliveryFirstName', $complaint, $complaintMessage);
        $this->assertSame($expectedComplaint->getDeliveryFirstName(), $complaint['deliveryFirstName'], $complaintMessage);

        $this->assertArrayHasKey('deliveryLastName', $complaint, $complaintMessage);
        $this->assertSame($expectedComplaint->getDeliveryLastName(), $complaint['deliveryLastName'], $complaintMessage);

        $this->assertArrayHasKey('deliveryCompanyName', $complaint, $complaintMessage);
        $this->assertSame($expectedComplaint->getDeliveryCompanyName(), $complaint['deliveryCompanyName'], $complaintMessage);

        $this->assertArrayHasKey('deliveryTelephone', $complaint, $complaintMessage);
        $this->assertSame($expectedComplaint->getDeliveryTelephone(), $complaint['deliveryTelephone'], $complaintMessage);

        $this->assertArrayHasKey('deliveryStreet', $complaint, $complaintMessage);
        $this->assertSame($expectedComplaint->getDeliveryStreet(), $complaint['deliveryStreet'], $complaintMessage);

        $this->assertArrayHasKey('deliveryCity', $complaint, $complaintMessage);
        $this->assertSame($expectedComplaint->getDeliveryCity(), $complaint['deliveryCity'], $complaintMessage);

        $this->assertArrayHasKey('deliveryPostcode', $complaint, $complaintMessage);
        $this->assertSame($expectedComplaint->getDeliveryPostcode(), $complaint['deliveryPostcode'], $complaintMessage);

        $this->assertArrayHasKey('status', $complaint, $complaintMessage);
        $this->assertSame($expectedComplaint->getStatus(), $complaint['status'], $complaintMessage);

        $this->assertArrayHasKey('items', $complaint, $complaintMessage);

        $expectedComplaintItems = $expectedComplaint->getItems();

        $this->assertSameSize($expectedComplaintItems, $complaint['items'], $complaintMessage);

        foreach ($complaint['items'] as $itemIndex => $complaintItem) {
            $expectedComplaintItem = $expectedComplaintItems[$itemIndex];

            $this->assertComplaintItem($expectedComplaintItem, $complaintItem);
        }
    }
}

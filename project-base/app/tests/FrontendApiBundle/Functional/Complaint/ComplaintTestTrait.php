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
     * @param array $actualComplaintItemData
     */
    protected function assertComplaintItem(ComplaintItem $expectedComplaintItem, array $actualComplaintItemData): void
    {
        $complaintItemMessage = sprintf(
            'Hint: check data of complaint item with ID #%d',
            $expectedComplaintItem->getId(),
        );

        $this->assertArrayHasKey('quantity', $actualComplaintItemData, $complaintItemMessage);
        $this->assertSame(
            $expectedComplaintItem->getQuantity(),
            $actualComplaintItemData['quantity'],
            $complaintItemMessage,
        );

        $this->assertArrayHasKey('description', $actualComplaintItemData, $complaintItemMessage);
        $this->assertSame(
            $expectedComplaintItem->getDescription(),
            $actualComplaintItemData['description'],
            $complaintItemMessage,
        );

        $this->assertArrayHasKey('orderItem', $actualComplaintItemData, $complaintItemMessage);

        $orderItem = $actualComplaintItemData['orderItem'];
        $expectedOrderItem = $expectedComplaintItem->getOrderItem();

        $this->assertArrayHasKey('uuid', $orderItem, $complaintItemMessage);
        $this->assertSame($expectedOrderItem->getUuid(), $orderItem['uuid'], $complaintItemMessage);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Complaint $expectedComplaint
     * @param array $actualComplaintData
     */
    protected function assertComplaint(Complaint $expectedComplaint, array $actualComplaintData): void
    {
        $complaintMessage = sprintf(
            'Hint: check data and sort of complaint with ID #%d',
            $expectedComplaint->getId(),
        );


        $this->assertArrayHasKey('uuid', $actualComplaintData, $complaintMessage);
        $this->assertSame($expectedComplaint->getUuid(), $actualComplaintData['uuid'], $complaintMessage);

        $this->assertArrayHasKey('number', $actualComplaintData, $complaintMessage);
        $this->assertSame($expectedComplaint->getNumber(), $actualComplaintData['number'], $complaintMessage);

        $this->assertArrayHasKey('createdAt', $actualComplaintData, $complaintMessage);
        $this->assertSame(
            DateTimeType::serialize($expectedComplaint->getCreatedAt()),
            $actualComplaintData['createdAt'],
            $complaintMessage,
        );

        $this->assertArrayHasKey('deliveryFirstName', $actualComplaintData, $complaintMessage);
        $this->assertSame(
            $expectedComplaint->getDeliveryFirstName(),
            $actualComplaintData['deliveryFirstName'],
            $complaintMessage,
        );

        $this->assertArrayHasKey('deliveryLastName', $actualComplaintData, $complaintMessage);
        $this->assertSame(
            $expectedComplaint->getDeliveryLastName(),
            $actualComplaintData['deliveryLastName'],
            $complaintMessage,
        );

        $this->assertArrayHasKey('deliveryCompanyName', $actualComplaintData, $complaintMessage);
        $this->assertSame(
            $expectedComplaint->getDeliveryCompanyName(),
            $actualComplaintData['deliveryCompanyName'],
            $complaintMessage,
        );

        $this->assertArrayHasKey('deliveryTelephone', $actualComplaintData, $complaintMessage);
        $this->assertSame(
            $expectedComplaint->getDeliveryTelephone(),
            $actualComplaintData['deliveryTelephone'],
            $complaintMessage,
        );

        $this->assertArrayHasKey('deliveryStreet', $actualComplaintData, $complaintMessage);
        $this->assertSame(
            $expectedComplaint->getDeliveryStreet(),
            $actualComplaintData['deliveryStreet'],
            $complaintMessage,
        );

        $this->assertArrayHasKey('deliveryCity', $actualComplaintData, $complaintMessage);
        $this->assertSame(
            $expectedComplaint->getDeliveryCity(),
            $actualComplaintData['deliveryCity'],
            $complaintMessage,
        );

        $this->assertArrayHasKey('deliveryPostcode', $actualComplaintData, $complaintMessage);
        $this->assertSame(
            $expectedComplaint->getDeliveryPostcode(),
            $actualComplaintData['deliveryPostcode'],
            $complaintMessage,
        );

        $this->assertArrayHasKey('status', $actualComplaintData, $complaintMessage);
        $this->assertSame(
            $expectedComplaint->getStatus()->getName($this->getFirstDomainLocale()),
            $actualComplaintData['status'],
            $complaintMessage,
        );

        $this->assertArrayHasKey('items', $actualComplaintData, $complaintMessage);

        $expectedComplaintItems = $expectedComplaint->getItems();

        $this->assertSameSize($expectedComplaintItems, $actualComplaintData['items'], $complaintMessage);

        foreach ($actualComplaintData['items'] as $itemIndex => $complaintItem) {
            $expectedComplaintItem = $expectedComplaintItems[$itemIndex];

            $this->assertComplaintItem($expectedComplaintItem, $complaintItem);
        }
    }
}

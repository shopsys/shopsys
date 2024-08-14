<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\Model\Order\Item\OrderItem;

trait OrderItemsTestTrait
{
    /**
     * @param array $orderItemIds
     * @return \App\Model\Order\Item\OrderItem[]
     */
    protected function getExpectedOrderItems(array $orderItemIds): array
    {
        $ordersArray = [];

        $orderItems = $this->em->createQueryBuilder()
            ->from(OrderItem::class, 'oi', 'oi.id')
            ->select('oi')
            ->getQuery()
            ->getResult();

        foreach ($orderItemIds as $orderId) {
            $ordersArray[] = $orderItems[$orderId];
        }

        return $ordersArray;
    }

    /**
     * @param array $responseData
     * @param array $expectedOrderItems
     * @param array $expectedOrderItemsIds
     */
    protected function assertOrderItemConnection(
        array $responseData,
        array $expectedOrderItems,
        array $expectedOrderItemsIds,
    ): void {
        $this->assertArrayHasKey('edges', $responseData);
        $this->assertSameSize(
            $expectedOrderItems,
            $responseData['edges'],
        );

        foreach ($responseData['edges'] as $orderIndex => $edge) {
            $orderMessage = sprintf(
                'Hint: check data and sort of order item with ID #%d',
                $expectedOrderItemsIds[$orderIndex],
            );

            $this->assertArrayHasKey('node', $edge, $orderMessage);

            /** @var \App\Model\Order\Item\OrderItem $expectedOrderData */
            $expectedOrderData = array_shift($expectedOrderItems);
            $node = $edge['node'];

            $this->assertArrayHasKey('uuid', $node, $orderMessage);
            $this->assertSame($expectedOrderData->getUuid(), $node['uuid'], $orderMessage);
            $this->assertArrayHasKey('name', $node, $orderMessage);
            $this->assertSame($expectedOrderData->getName(), $node['name'], $orderMessage);
            $this->assertArrayHasKey('vatRate', $node, $orderMessage);
            $this->assertSame($expectedOrderData->getVatPercent(), $node['vatRate'], $orderMessage);
            $this->assertArrayHasKey('quantity', $node, $orderMessage);
            $this->assertSame($expectedOrderData->getQuantity(), $node['quantity'], $orderMessage);
            $this->assertArrayHasKey('unit', $node, $orderMessage);

            $this->assertArrayHasKey('order', $node, $orderMessage);
            $this->assertArrayHasKey('number', $node['order'], $orderMessage);
            $this->assertSame($expectedOrderData->getOrder()->getNumber(), $node['order']['number'], $orderMessage);
        }
    }
}

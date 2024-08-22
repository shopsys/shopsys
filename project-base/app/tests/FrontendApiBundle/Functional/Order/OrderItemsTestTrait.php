<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\Model\Order\Item\OrderItem;

trait OrderItemsTestTrait
{
    /**
     * @param int[] $orderItemIds
     * @return \App\Model\Order\Item\OrderItem[]
     */
    protected function getExpectedOrderItems(array $orderItemIds): array
    {
        $orderItemsArray = [];

        $orderItems = $this->em->createQueryBuilder()
            ->from(OrderItem::class, 'oi', 'oi.id')
            ->select('oi')
            ->andWhere('oi.id IN (:orderItemIds)')
            ->setParameter('orderItemIds', $orderItemIds)
            ->getQuery()
            ->getResult();

        foreach ($orderItemIds as $orderItemId) {
            $orderItemsArray[] = $orderItems[$orderItemId];
        }

        return $orderItemsArray;
    }

    /**
     * @param array $responseData
     * @param \App\Model\Order\Item\OrderItem[] $expectedOrderItems
     * @param int[] $expectedOrderItemsIds
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
            $orderItemMessage = sprintf(
                'Hint: check data and sort of order item with ID #%d',
                $expectedOrderItemsIds[$orderIndex],
            );

            $this->assertArrayHasKey('node', $edge, $orderItemMessage);

            /** @var \App\Model\Order\Item\OrderItem $expectedOrderItem */
            $expectedOrderItem = array_shift($expectedOrderItems);
            $node = $edge['node'];

            $this->assertArrayHasKey('uuid', $node, $orderItemMessage);
            $this->assertSame($expectedOrderItem->getUuid(), $node['uuid'], $orderItemMessage);
            $this->assertArrayHasKey('name', $node, $orderItemMessage);
            $this->assertSame($expectedOrderItem->getName(), $node['name'], $orderItemMessage);
            $this->assertArrayHasKey('vatRate', $node, $orderItemMessage);
            $this->assertSame($expectedOrderItem->getVatPercent(), $node['vatRate'], $orderItemMessage);
            $this->assertArrayHasKey('quantity', $node, $orderItemMessage);
            $this->assertSame($expectedOrderItem->getQuantity(), $node['quantity'], $orderItemMessage);
            $this->assertArrayHasKey('unit', $node, $orderItemMessage);
            $this->assertSame($expectedOrderItem->getUnitName(), $node['unit'], $orderItemMessage);
            $this->assertArrayHasKey('type', $node, $orderItemMessage);
            $this->assertSame($expectedOrderItem->getType(), $node['type'], $orderItemMessage);

            $this->assertArrayHasKey('order', $node, $orderItemMessage);
            $this->assertArrayHasKey('number', $node['order'], $orderItemMessage);
            $this->assertSame($expectedOrderItem->getOrder()->getNumber(), $node['order']['number'], $orderItemMessage);
        }
    }
}

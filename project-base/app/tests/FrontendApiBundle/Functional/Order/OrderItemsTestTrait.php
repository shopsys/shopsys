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
            $orderMessage = sprintf(
                'Hint: check data and sort of order item with ID #%d',
                $expectedOrderItemsIds[$orderIndex],
            );

            $this->assertArrayHasKey('node', $edge, $orderMessage);

            /** @var \App\Model\Order\Item\OrderItem $expectedOrderItem */
            $expectedOrderItem = array_shift($expectedOrderItems);
            $node = $edge['node'];

            $this->assertArrayHasKey('uuid', $node, $orderMessage);
            $this->assertSame($expectedOrderItem->getUuid(), $node['uuid'], $orderMessage);
            $this->assertArrayHasKey('name', $node, $orderMessage);
            $this->assertSame($expectedOrderItem->getName(), $node['name'], $orderMessage);
            $this->assertArrayHasKey('vatRate', $node, $orderMessage);
            $this->assertSame($expectedOrderItem->getVatPercent(), $node['vatRate'], $orderMessage);
            $this->assertArrayHasKey('quantity', $node, $orderMessage);
            $this->assertSame($expectedOrderItem->getQuantity(), $node['quantity'], $orderMessage);
            $this->assertArrayHasKey('unit', $node, $orderMessage);

            $this->assertArrayHasKey('order', $node, $orderMessage);
            $this->assertArrayHasKey('number', $node['order'], $orderMessage);
            $this->assertSame($expectedOrderItem->getOrder()->getNumber(), $node['order']['number'], $orderMessage);
        }
    }
}

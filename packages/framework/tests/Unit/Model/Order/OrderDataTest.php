<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\OrderData;

class OrderDataTest extends TestCase
{
    public function testGetNewItemsWithoutTransportAndPayment()
    {
        $orderData = new OrderData();
        $newOrderItemData = new OrderItemData();
        $oldOrderItemData = new OrderItemData();
        $items = [
            OrderData::NEW_ITEM_PREFIX . '1' => $newOrderItemData,
            1 => $oldOrderItemData,
        ];
        $orderData->items = $items;

        $newItems = $orderData->getNewItemsWithoutTransportAndPayment();

        $this->assertSame([$newOrderItemData], $newItems);
    }
}

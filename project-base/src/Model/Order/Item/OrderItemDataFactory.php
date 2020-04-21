<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData as BaseOrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory as BaseOrderItemDataFactory;

/**
 * @method \App\Model\Order\Item\OrderItemData create()
 * @method \App\Model\Order\Item\OrderItemData createFromOrderItem(\App\Model\Order\Item\OrderItem $orderItem)
 */
class OrderItemDataFactory extends BaseOrderItemDataFactory
{
    /**
     * @return \App\Model\Order\Item\OrderItemData
     */
    protected function createInstance(): BaseOrderItemData
    {
        return new OrderItemData();
    }
}

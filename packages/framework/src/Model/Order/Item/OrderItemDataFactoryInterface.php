<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

interface OrderItemDataFactoryInterface
{
    public function create(): OrderItemData;

    public function createFromOrderItem(OrderItem $orderItem): OrderItemData;
}

<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status;

interface OrderStatusDataFactoryInterface
{
    public function create(): OrderStatusData;

    public function createFromOrderStatus(OrderStatus $orderStatus): OrderStatusData;
}

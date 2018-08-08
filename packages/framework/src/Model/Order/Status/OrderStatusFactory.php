<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status;

class OrderStatusFactory implements OrderStatusFactoryInterface
{
    public function create(OrderStatusData $data, int $type): OrderStatus
    {
        return new OrderStatus($data, $type);
    }
}

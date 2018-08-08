<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status;

interface OrderStatusFactoryInterface
{
    public function create(OrderStatusData $data, int $type): OrderStatus;
}

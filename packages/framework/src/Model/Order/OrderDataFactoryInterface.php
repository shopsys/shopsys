<?php

namespace Shopsys\FrameworkBundle\Model\Order;

interface OrderDataFactoryInterface
{
    public function create(): OrderData;

    public function createFromOrder(Order $order): OrderData;
}

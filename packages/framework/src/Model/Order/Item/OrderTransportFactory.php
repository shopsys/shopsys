<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class OrderTransportFactory implements OrderTransportFactoryInterface
{
    public function create(
        Order $order,
        string $name,
        Price $price,
        string $vatPercent,
        int $quantity,
        Transport $transport
    ): OrderTransport {
        return new OrderTransport(
            $order,
            $name,
            $price,
            $vatPercent,
            $quantity,
            $transport
        );
    }
}

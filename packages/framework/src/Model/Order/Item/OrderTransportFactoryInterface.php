<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

interface OrderTransportFactoryInterface
{
    public function create(
        Order $order,
        string $name,
        Price $price,
        string $vatPercent,
        int $quantity,
        Transport $transport
    ): OrderTransport;
}

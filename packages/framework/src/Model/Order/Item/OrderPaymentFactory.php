<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class OrderPaymentFactory implements OrderPaymentFactoryInterface
{

    public function create(
        Order $order,
        string $name,
        Price $price,
        string $vatPercent,
        int $quantity,
        Payment $payment
    ): OrderPayment {
        return new OrderPayment(
            $order,
            $name,
            $price,
            $vatPercent,
            $quantity,
            $payment
        );
    }
}

<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

interface PaymentPriceFactoryInterface
{
    public function create(
        Payment $payment,
        Currency $currency,
        string $price
    ): PaymentPrice;
}

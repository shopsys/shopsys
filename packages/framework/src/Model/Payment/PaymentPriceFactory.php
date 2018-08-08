<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class PaymentPriceFactory implements PaymentPriceFactoryInterface
{

    public function create(
        Payment $payment,
        Currency $currency,
        string $price
    ): PaymentPrice {
        return new PaymentPrice($payment, $currency, $price);
    }
}

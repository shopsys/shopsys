<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Money\Money;

interface PaymentPriceFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentPrice
     */
    public function create(
        Payment $payment,
        Money $price,
        int $domainId,
    ): PaymentPrice;
}

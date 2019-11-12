<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

interface PaymentPriceFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentPrice
     * @deprecated Will be removed in the next major release, use PaymentPriceFactoryInterface::createByCurrencyAndDomainId instead
     */
    public function create(
        Payment $payment,
        Currency $currency,
        Money $price,
        Vat $vat,
        int $domainId
    ): PaymentPrice;
}

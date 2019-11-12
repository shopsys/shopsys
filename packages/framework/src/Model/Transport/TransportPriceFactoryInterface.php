<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

interface TransportPriceFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportPrice
     */
    public function create(
        Transport $transport,
        Currency $currency,
        Money $price,
        Vat $vat,
        int $domainId
    ): TransportPrice;
}

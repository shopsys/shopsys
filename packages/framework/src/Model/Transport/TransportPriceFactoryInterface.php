<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

interface TransportPriceFactoryInterface
{
    public function create(
        Transport $transport,
        Currency $currency,
        string $price
    ): TransportPrice;
}

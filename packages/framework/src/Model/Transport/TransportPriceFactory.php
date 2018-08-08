<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class TransportPriceFactory implements TransportPriceFactoryInterface
{
    public function create(
        Transport $transport,
        Currency $currency,
        string $price
    ): TransportPrice {
        return new TransportPrice($transport, $currency, $price);
    }
}

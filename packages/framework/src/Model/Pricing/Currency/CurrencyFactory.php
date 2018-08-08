<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

class CurrencyFactory implements CurrencyFactoryInterface
{
    public function create(CurrencyData $data): Currency
    {
        return new Currency($data);
    }
}

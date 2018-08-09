<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

interface CurrencyDataFactoryInterface
{
    public function create(): CurrencyData;

    public function createFromCurrency(Currency $currency): CurrencyData;
}

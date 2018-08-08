<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

interface CurrencyFactoryInterface
{

    public function create(CurrencyData $data): Currency;
}

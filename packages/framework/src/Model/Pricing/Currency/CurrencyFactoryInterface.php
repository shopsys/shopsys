<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

interface CurrencyFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $data
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function create(CurrencyData $data): Currency;
}

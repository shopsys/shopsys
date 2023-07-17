<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test\Provider;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;

class TestCurrencyProvider
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public static function getTestCurrency(): Currency
    {
        $currencyData = new CurrencyData();
        $currencyData->name = 'currencyName';
        $currencyData->code = Currency::CODE_CZK;
        $currencyData->exchangeRate = '1.0';
        $currencyData->minFractionDigits = 2;
        $currencyData->roundingType = Currency::ROUNDING_TYPE_INTEGER;

        return new Currency($currencyData);
    }
}

<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

class CurrencyDataFactory implements CurrencyDataFactoryInterface
{
    public function create(): CurrencyData
    {
        return new CurrencyData();
    }

    public function createFromCurrency(Currency $currency): CurrencyData
    {
        $currencyData = new CurrencyData();
        $this->fillFromCurrency($currencyData, $currency);

        return $currencyData;
    }

    protected function fillFromCurrency(CurrencyData $currencyData, Currency $currency): void
    {
        $currencyData->name = $currency->getName();
        $currencyData->code = $currency->getCode();
        $currencyData->exchangeRate = $currency->getExchangeRate();
    }
}

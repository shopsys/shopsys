<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

class CurrencyDataFactory
{
    protected function createInstance(): CurrencyData
    {
        return new CurrencyData();
    }

    public function create(): CurrencyData
    {
        return $this->createInstance();
    }

    public function createFromCurrency(Currency $currency): CurrencyData
    {
        $currencyData = $this->createInstance();
        $this->fillFromCurrency($currencyData, $currency);

        return $currencyData;
    }

    protected function fillFromCurrency(CurrencyData $currencyData, Currency $currency): void
    {
        $currencyData->name = $currency->getName();
        $currencyData->code = $currency->getCode();
        $currencyData->exchangeRate = $currency->getExchangeRate();
        $currencyData->minFractionDigits = $currency->getMinFractionDigits();
        $currencyData->roundingType = $currency->getRoundingType();
        $currencyData->roundingPlacesPriceWithoutVat = $currency->getRoundingPlacesPriceWithoutVat();
    }
}

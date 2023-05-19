<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

class CurrencyDataFactory implements CurrencyDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData
     */
    protected function createInstance(): CurrencyData
    {
        return new CurrencyData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData
     */
    public function create(): CurrencyData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData
     */
    public function createFromCurrency(Currency $currency): CurrencyData
    {
        $currencyData = $this->createInstance();
        $this->fillFromCurrency($currencyData, $currency);

        return $currencyData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $currencyData
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     */
    protected function fillFromCurrency(CurrencyData $currencyData, Currency $currency)
    {
        $currencyData->name = $currency->getName();
        $currencyData->code = $currency->getCode();
        $currencyData->exchangeRate = $currency->getExchangeRate();
        $currencyData->minFractionDigits = $currency->getMinFractionDigits();
        $currencyData->roundingType = $currency->getRoundingType();
    }
}

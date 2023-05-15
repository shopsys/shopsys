<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class PriceConverter
{
    protected const DEFAULT_SCALE = 2;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Rounding $rounding
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(protected readonly CurrencyFacade $currencyFacade, protected readonly Rounding $rounding, protected readonly Setting $setting)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $priceCurrency
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function convertPriceWithoutVatToDomainDefaultCurrencyPrice(Money $price, Currency $priceCurrency, int $domainId): Money
    {
        $domainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $price = $this->convertPriceToPriceInDomainDefaultCurrency($price, $priceCurrency, $domainDefaultCurrency);

        return $this->rounding->roundPriceWithoutVat($price);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $priceCurrency
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function convertPriceWithVatToDomainDefaultCurrencyPrice(Money $price, Currency $priceCurrency, int $domainId): Money
    {
        $domainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $price = $this->convertPriceToPriceInDomainDefaultCurrency($price, $priceCurrency, $domainDefaultCurrency);

        return $this->rounding->roundPriceWithVatByCurrency($price, $domainDefaultCurrency);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $priceCurrency
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $domainDefaultCurrency
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function convertPriceToPriceInDomainDefaultCurrency(Money $price, Currency $priceCurrency, Currency $domainDefaultCurrency): Money
    {
        $coefficient = $this->currencyFacade->getExchangeRateForCurrencies($priceCurrency, $domainDefaultCurrency);

        return $price->multiply((string)$coefficient);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param string $vatPercent
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function convertPriceToInputPriceWithoutVatInDomainDefaultCurrency(
        Money $price,
        Currency $currency,
        string $vatPercent,
        int $domainId,
    ): Money {
        if ($this->setting->get(PricingSetting::INPUT_PRICE_TYPE) === PricingSetting::INPUT_PRICE_TYPE_WITH_VAT) {
            $multiplier = (string)(100 + (float)$vatPercent);

            $price = $price
                ->multiply($multiplier)
                ->divide(100, 6);
        }

        return $this->convertPriceWithoutVatToDomainDefaultCurrencyPrice($price, $currency, $domainId);
    }
}

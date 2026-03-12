<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class PriceConverter
{
    public function __construct(
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly Rounding $rounding,
        protected readonly Setting $setting,
    ) {
    }

    public function convertPriceWithoutVatToDomainDefaultCurrencyPrice(
        Money $price,
        Currency $priceCurrency,
        int $domainId,
    ): Money {
        $domainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $price = $this->convertPriceToPriceInDomainDefaultCurrency($price, $priceCurrency, $domainDefaultCurrency);

        return $this->rounding->roundPriceWithoutVat($price, $domainDefaultCurrency->getRoundingPlacesPriceWithoutVat());
    }

    public function convertPriceWithVatToDomainDefaultCurrencyPrice(
        Money $price,
        Currency $priceCurrency,
        int $domainId,
    ): Money {
        $domainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $price = $this->convertPriceToPriceInDomainDefaultCurrency($price, $priceCurrency, $domainDefaultCurrency);

        return $this->rounding->roundPriceWithVat($price, $domainDefaultCurrency->getRoundingType());
    }

    protected function convertPriceToPriceInDomainDefaultCurrency(
        Money $price,
        Currency $priceCurrency,
        Currency $domainDefaultCurrency,
    ): Money {
        $coefficient = $this->currencyFacade->getExchangeRateForCurrencies($priceCurrency, $domainDefaultCurrency);

        return $price->multiply((string)$coefficient);
    }

    public function convertPriceToInputPriceInDomainDefaultCurrency(
        Money $price,
        Currency $currency,
        string $vatPercent,
        int $domainId,
    ): Money {
        if ($this->setting->get(PricingSetting::INPUT_PRICE_TYPE) === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $multiplier = (string)(100 + (float)$vatPercent);

            $price = $price
                ->multiply($multiplier)
                ->divide(100, 6);
        }

        return $this->convertPriceWithoutVatToDomainDefaultCurrencyPrice($price, $currency, $domainId);
    }
}

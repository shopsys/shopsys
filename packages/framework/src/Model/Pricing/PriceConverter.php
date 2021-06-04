<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class PriceConverter
{
    protected const DEFAULT_SCALE = 2;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Rounding
     */
    protected $rounding;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Rounding $rounding
     */
    public function __construct(CurrencyFacade $currencyFacade, Rounding $rounding)
    {
        $this->currencyFacade = $currencyFacade;
        $this->rounding = $rounding;
    }

    /**
     * @deprecated use convertPriceWithoutVatToDomainDefaultCurrencyPrice() instead
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function convertPriceWithoutVatToPriceInDomainDefaultCurrency(Money $price, int $domainId): Money
    {
        DeprecationHelper::triggerMethod(__METHOD__, 'convertPriceWithoutVatToDomainDefaultCurrencyPrice');
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $price = $price->divide($currency->getExchangeRate(), static::DEFAULT_SCALE);

        return $this->rounding->roundPriceWithoutVat($price);
    }

    /**
     * @deprecated use convertPriceWithVatToDomainDefaultCurrencyPrice() instead
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function convertPriceWithVatToPriceInDomainDefaultCurrency(Money $price, int $domainId): Money
    {
        DeprecationHelper::triggerMethod(__METHOD__, 'convertPriceWithVatToDomainDefaultCurrencyPrice');
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $price = $price->divide($currency->getExchangeRate(), static::DEFAULT_SCALE);

        return $this->rounding->roundPriceWithVatByCurrency($price, $currency);
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
}

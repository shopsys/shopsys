<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
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
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function convertPriceWithoutVatToPriceInDomainDefaultCurrency(Money $price, int $domainId): Money
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $price = $price->divide($currency->getExchangeRate(), static::DEFAULT_SCALE);

        return $this->rounding->roundPriceWithoutVat($price);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function convertPriceWithVatToPriceInDomainDefaultCurrency(Money $price, int $domainId): Money
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $price = $price->divide($currency->getExchangeRate(), static::DEFAULT_SCALE);

        return $this->rounding->roundPriceWithVatByCurrency($price, $currency);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class PriceCalculation
{
    protected const int PRICE_CALCULATION_MAX_SCALE = 6;

    public function __construct(protected readonly Rounding $rounding)
    {
    }

    public function getVatAmountByPriceWithVat(Money $priceWithVat, Vat $vat, Currency $currency): Money
    {
        $divisor = (string)(1 + (float)$vat->getPercent() / 100);

        $priceWithoutVat = $priceWithVat->divide($divisor, static::PRICE_CALCULATION_MAX_SCALE);

        return $this->rounding->roundVatAmount($priceWithVat->subtract($priceWithoutVat), $currency);
    }

    public function getVatAmountByPriceWithVatForVatPercent(
        Money $priceWithVat,
        float $vatPercent,
        Currency $currency,
    ): Money {
        $divisor = (string)(1 + $vatPercent / 100);

        $priceWithoutVat = $priceWithVat->divide($divisor, static::PRICE_CALCULATION_MAX_SCALE);

        return $this->rounding->roundVatAmount($priceWithVat->subtract($priceWithoutVat), $currency);
    }

    public function applyVatPercent(Money $priceWithoutVat, Vat $vat): Money
    {
        return $this->applyVatByPercent($priceWithoutVat, (float)$vat->getPercent());
    }

    public function applyVatByPercent(Money $priceWithoutVat, float $vatPercent): Money
    {
        $multiplier = (string)(1 + $vatPercent / 100);

        return $priceWithoutVat->multiply($multiplier);
    }
}

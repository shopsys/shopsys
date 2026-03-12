<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\InvalidCurrencyRoundingTypeException;

class Rounding
{
    public function roundPriceWithVat(Money $priceWithVat, string $roundingType): Money
    {
        switch ($roundingType) {
            case Currency::ROUNDING_TYPE_HUNDREDTHS:
                return $priceWithVat->round(2);

            case Currency::ROUNDING_TYPE_FIFTIES:
                return $priceWithVat->multiply(2)->round(0)->divide(2, 1);

            case Currency::ROUNDING_TYPE_INTEGER:
                return $priceWithVat->round(0);

            default:
                throw new InvalidCurrencyRoundingTypeException($roundingType);
        }
    }

    public function roundPriceWithoutVat(Money $priceWithoutVat, int $roundingPlaces): Money
    {
        return $priceWithoutVat->round($roundingPlaces);
    }

    public function roundVatAmount(Money $vatAmount, int $roundingPlaces): Money
    {
        return $vatAmount->round($roundingPlaces);
    }
}

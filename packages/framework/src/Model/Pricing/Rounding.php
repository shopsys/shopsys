<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\InvalidCurrencyRoundingTypeException;

class Rounding
{
    public function roundPriceWithVatByCurrency(Money $priceWithVat, Currency $currency): Money
    {
        $roundingType = $currency->getRoundingType();

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

    public function roundPriceWithoutVat(Money $priceWithoutVat, Currency $currency): Money
    {
        return $priceWithoutVat->round($currency->getRoundingPlacesPriceWithoutVat());
    }

    public function roundVatAmount(Money $vatAmount, Currency $currency): Money
    {
        return $vatAmount->round($currency->getRoundingPlacesPriceWithoutVat());
    }
}

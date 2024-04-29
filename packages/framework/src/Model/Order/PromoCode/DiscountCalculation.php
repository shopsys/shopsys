<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;

class DiscountCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Rounding $rounding
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation $priceCalculation
     */
    public function __construct(
        protected readonly Rounding $rounding,
        protected readonly PriceCalculation $priceCalculation,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalItemPrice
     * @param float $vatPercent
     * @param float $discountPercent
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    public function calculatePercentageDiscountRoundedByCurrency(
        Price $totalItemPrice,
        float $vatPercent,
        float $discountPercent,
        Currency $currency,
    ): ?Price {
        $multiplier = (string)($discountPercent / 100);

        $priceWithVat = $this->rounding->roundPriceWithVatByCurrency(
            $totalItemPrice->getPriceWithVat()->multiply($multiplier),
            $currency,
        );

        if ($priceWithVat->isZero()) {
            return null;
        }

        $priceVatAmount = $this->priceCalculation->getVatAmountByPriceWithVatForVatPercent($priceWithVat, $vatPercent);
        $priceWithoutVat = $priceWithVat->subtract($priceVatAmount);

        return new Price($priceWithoutVat, $priceWithVat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithVat
     * @param float $vatPercent
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function calculateNominalDiscount(
        Money $priceWithVat,
        float $vatPercent,
    ): Price {
        $priceVatAmount = $this->priceCalculation->getVatAmountByPriceWithVatForVatPercent($priceWithVat, $vatPercent);
        $priceWithoutVat = $priceWithVat->subtract($priceVatAmount);

        return new Price($priceWithoutVat, $priceWithVat);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;

class DiscountCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Rounding $rounding
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation $priceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     */
    public function __construct(
        protected readonly Rounding $rounding,
        protected readonly PriceCalculation $priceCalculation,
        protected readonly PricingSetting $pricingSetting,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $totalItemPrice
     * @param float $vatPercent
     * @param float $discountPercent
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    public function calculatePercentageDiscountRoundedByCurrency(
        PriceInterface $totalItemPrice,
        float $vatPercent,
        float $discountPercent,
        Currency $currency,
    ): ?PriceInterface {
        $multiplier = (string)($discountPercent / 100);

        $priceWithVat = $this->rounding->roundPriceWithVatByCurrency(
            $totalItemPrice->getPriceWithVat()->multiply($multiplier),
            $currency,
        );

        if ($priceWithVat->isZero()) {
            return null;
        }

        $priceVatAmount = $this->priceCalculation->getVatAmountByPriceWithVatForVatPercent($priceWithVat, $vatPercent, $currency);
        $priceWithoutVat = $priceWithVat->subtract($priceVatAmount);

        return new Price($priceWithoutVat, $priceWithVat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $discount
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $totalPrice
     * @param float $vatPercent
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function calculateNominalDiscount(
        Money $discount,
        PriceInterface $totalPrice,
        float $vatPercent,
        Currency $currency,
    ): PriceInterface {
        if ($this->pricingSetting->getInputPriceType() === PricingSetting::INPUT_PRICE_TYPE_WITH_VAT) {
            if ($discount->isGreaterThan($totalPrice->getPriceWithVat())) {
                return $totalPrice;
            }

            $priceVatAmount = $this->priceCalculation->getVatAmountByPriceWithVatForVatPercent($discount, $vatPercent, $currency);
            $priceWithoutVat = $discount->subtract($priceVatAmount);

            return new Price($priceWithoutVat, $discount);
        }

        if ($discount->isGreaterThan($totalPrice->getPriceWithoutVat())) {
            return $totalPrice;
        }

        $priceWithVat = $this->priceCalculation->applyVatByPercent($discount, $vatPercent);

        return new Price($discount, $priceWithVat);
    }
}

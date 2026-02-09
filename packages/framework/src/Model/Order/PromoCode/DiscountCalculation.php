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
    public function __construct(
        protected readonly Rounding $rounding,
        protected readonly PriceCalculation $priceCalculation,
        protected readonly PricingSetting $pricingSetting,
    ) {
    }

    /**
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

        if ($this->pricingSetting->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $priceVatAmount = $this->priceCalculation->getVatAmountByPriceWithVatForVatPercent($priceWithVat, $vatPercent, $currency);
            $priceWithoutVat = $priceWithVat->subtract($priceVatAmount);
        } else {
            $priceWithoutVat = $this->rounding->roundPriceWithoutVat(
                $totalItemPrice->getPriceWithoutVat()->multiply($multiplier),
                $currency,
            );

            $priceWithVat = $this->rounding->roundPriceWithVatByCurrency(
                $this->priceCalculation->applyVatByPercent($priceWithoutVat, $vatPercent),
                $currency,
            );
        }

        return new Price($priceWithoutVat, $priceWithVat);
    }

    public function calculateNominalDiscount(
        Money $discount,
        PriceInterface $totalPrice,
        float $vatPercent,
        Currency $currency,
    ): PriceInterface {
        if ($this->pricingSetting->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
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

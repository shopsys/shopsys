<?php

declare(strict_types=1);

namespace App\Model\Product\Pricing;

use App\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductDiscountCalculation as BaseQuantifiedProductDiscountCalculation;

class QuantifiedProductDiscountCalculation extends BaseQuantifiedProductDiscountCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param string[] $discountPercentPerProduct
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    public function calculateDiscountsPerProductRoundedByCurrency(array $quantifiedProducts, array $quantifiedItemsPrices, array $discountPercentPerProduct, Currency $currency): array
    {
        $quantifiedItemsDiscounts = [];
        foreach ($quantifiedProducts as $quantifiedItemIndex => $quantifiedProduct) {
            $productId = $quantifiedProduct->getProduct()->getId();
            if (array_key_exists($productId, $discountPercentPerProduct)) {
                $quantifiedItemsDiscounts[$quantifiedItemIndex] = $this->calculateRoundedDiscountByPromoCode(
                    $quantifiedItemsPrices[$quantifiedItemIndex],
                    $discountPercentPerProduct[$productId],
                    $currency,
                    $quantifiedProduct
                );
            } else {
                $quantifiedItemsDiscounts[$quantifiedItemIndex] = null;
            }
        }

        return $quantifiedItemsDiscounts;
    }

    /**
     * @param \App\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $quantifiedItemsDiscounts
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]|null[]
     */
    public function calculateDiscountPricesPerProductRoundedByCurrency(array $quantifiedItemsPrices, array $quantifiedItemsDiscounts, Currency $currency): array
    {
        $quantifiedItemsDiscountPrices = [];
        foreach ($quantifiedItemsPrices as $quantifiedItemIndex => $quantifiedItemPrice) {
            $discount = $quantifiedItemsDiscounts[$quantifiedItemIndex] ?? null;

            if ($discount !== null) {
                $quantifiedItemsDiscountPrices[$quantifiedItemIndex] = $this->calculateDiscountPriceRoundedByCurrency(
                    $quantifiedItemPrice,
                    $discount,
                    $currency
                );
            } else {
                $quantifiedItemsDiscountPrices[$quantifiedItemIndex] = null;
            }
        }

        return $quantifiedItemsDiscountPrices;
    }

    /**
     * @param \App\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private function calculateRoundedDiscountByPromoCode(
        QuantifiedItemPrice $quantifiedItemPrice,
        PromoCode $promoCode,
        Currency $currency,
        QuantifiedProduct $quantifiedProduct
    ): ?Price {
        $vat = $quantifiedItemPrice->getVat();
        $discountMultiplier = (string)($promoCode->getPercent() / 100);
        if ($promoCode->isApplyOnSecondProduct()) {
            $quantity = $quantifiedProduct->getQuantity();
            $unitHighPriceWithVat = $quantifiedItemPrice->getUnitHighPrice()->getPriceWithVat();

            $discountedProductQuantity = intdiv($quantity, 2);
            $singleDiscountFromHighUnitPrice = $unitHighPriceWithVat->multiply($discountMultiplier);

            $singleDiscountWithVat = $this->rounding->roundPriceWithVatByCurrency(
                $singleDiscountFromHighUnitPrice,
                $currency
            );
            $discountWithVat = $singleDiscountWithVat->multiply($discountedProductQuantity);

            if ($discountWithVat->isZero()) {
                return null;
            }

            $discountVatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($discountWithVat, $vat);
            $discountWithoutVat = $discountWithVat->subtract($discountVatAmount);

            return new Price($discountWithoutVat, $discountWithVat);
        }

        return $this->calculateDiscountRoundedByCurrency($quantifiedItemPrice, $promoCode->getPercent(), $currency);
    }

    /**
     * @param \App\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $discount
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private function calculateDiscountPriceRoundedByCurrency(
        QuantifiedItemPrice $quantifiedItemPrice,
        Price $discount,
        Currency $currency
    ): ?Price {
        $vat = $quantifiedItemPrice->getVat();
        $priceWithVat = $this->rounding->roundPriceWithVatByCurrency(
            $quantifiedItemPrice->getTotalPrice()->getPriceWithVat()->subtract($discount->getPriceWithVat()),
            $currency
        );

        if ($priceWithVat->isZero()) {
            return null;
        }

        $priceVatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($priceWithVat, $vat);
        $priceWithoutVat = $priceWithVat->subtract($priceVatAmount);

        return new Price($priceWithoutVat, $priceWithVat);
    }
}

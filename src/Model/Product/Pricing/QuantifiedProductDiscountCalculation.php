<?php

declare(strict_types=1);

namespace App\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;
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
                $quantifiedItemsDiscounts[$quantifiedItemIndex] = $this->calculateDiscountRoundedByCurrency(
                    $quantifiedItemsPrices[$quantifiedItemIndex],
                    $discountPercentPerProduct[$productId],
                    $currency
                );
            } else {
                $quantifiedItemsDiscounts[$quantifiedItemIndex] = null;
            }
        }

        return $quantifiedItemsDiscounts;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param string[] $discountPercentPerProduct
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]|null[]
     */
    public function calculateDiscountPricesPerProductRoundedByCurrency(array $quantifiedProducts, array $quantifiedItemsPrices, array $discountPercentPerProduct, Currency $currency): array
    {
        $quantifiedItemsDiscountPrices = [];
        foreach ($quantifiedProducts as $quantifiedItemIndex => $quantifiedProduct) {
            $productId = $quantifiedProduct->getProduct()->getId();
            if (array_key_exists($productId, $discountPercentPerProduct)) {
                $quantifiedItemsDiscountPrices[$quantifiedItemIndex] = $this->calculateDiscountPriceRoundedByCurrency(
                    $quantifiedItemsPrices[$quantifiedItemIndex],
                    $discountPercentPerProduct[$productId],
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
     * @param string $discountPercent
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private function calculateDiscountPriceRoundedByCurrency(
        QuantifiedItemPrice $quantifiedItemPrice,
        string $discountPercent,
        Currency $currency
    ): ?Price {
        $vat = $quantifiedItemPrice->getVat();
        $multiplier = (string)($discountPercent / 100);
        $discountFromLowPrice = $quantifiedItemPrice->getTotalPrice()->getPriceWithVat()->multiply($multiplier);
        $priceWithVat = $this->rounding->roundPriceWithVatByCurrency(
            $quantifiedItemPrice->getTotalHighPrice()->getPriceWithVat()->subtract($discountFromLowPrice),
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

<?php

declare(strict_types=1);

namespace App\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
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
}

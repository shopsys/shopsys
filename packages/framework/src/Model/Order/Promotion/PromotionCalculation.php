<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Promotion;

use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class PromotionCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $unitPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $totalPrice
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function calculateTotalPrice(
        QuantifiedProduct $quantifiedProduct,
        PriceInterface $unitPrice,
        PriceInterface $totalPrice,
    ): PriceInterface {
        $product = $quantifiedProduct->getProduct();
        $promotionX = $product->getPromotionX();
        $promotionY = $product->getPromotionY();

        if ($promotionX === null || $promotionY === null) {
            return $totalPrice;
        }

        $quantity = $quantifiedProduct->getQuantity();
        $fullPromotion = $promotionX + $promotionY;
        $fullGroups = intdiv($quantity, $fullPromotion);
        $remainder = $quantity % $fullPromotion;
        $extra = max(0, min($remainder - $promotionX, $promotionY));
        $freebies = $fullGroups * $promotionY + $extra;

        if ($freebies <= 0) {
            return $totalPrice;
        }

        $discountWithVat = $unitPrice->getPriceWithVat()->multiply($freebies);
        $discountWithoutVat = $unitPrice->getPriceWithoutVat()->multiply($freebies);

        $newWithVat = $totalPrice->getPriceWithVat()->subtract($discountWithVat);
        $newWithoutVat = $totalPrice->getPriceWithoutVat()->subtract($discountWithoutVat);

        return new Price($newWithoutVat, $newWithVat);
    }
}

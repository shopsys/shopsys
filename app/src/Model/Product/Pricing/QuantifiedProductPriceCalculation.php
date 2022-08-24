<?php

declare(strict_types=1);

namespace App\Model\Product\Pricing;

use App\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation as BaseQuantifiedProductPriceCalculation;

class QuantifiedProductPriceCalculation extends BaseQuantifiedProductPriceCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param int $domainId
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param \App\Model\Order\PromoCode\PromoCode|null $promoCode
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice
     */
    public function calculatePrice(QuantifiedProduct $quantifiedProduct, int $domainId, ?CustomerUser $customerUser = null, ?PromoCode $promoCode = null): QuantifiedItemPrice
    {
        /** @var \App\Model\Product\Product $product */
        $product = $quantifiedProduct->getProduct();

        $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCustomerUserAndDomainId(
            $product,
            $domainId,
            $customerUser
        );

        if ($promoCode !== null) {
            if ($promoCode->isApplyOnSecondProduct()) {
                $quantity = $quantifiedProduct->getQuantity();
                $productSellingPriceQuantity = $quantity % 2;
                $totalPriceDiscountedProducts = $productPrice->getPriceWithVat()->multiply($quantity - $productSellingPriceQuantity);
                $totalPriceProducts = $productPrice->getPriceWithVat()->multiply($productSellingPriceQuantity);

                $totalPriceWithVat = $totalPriceDiscountedProducts->add($totalPriceProducts);
            } else {
                $totalPriceWithVat = $this->getTotalPriceWithVat($quantifiedProduct, $productPrice);
            }
        } else {
            $totalPriceWithVat = $this->getTotalPriceWithVat($quantifiedProduct, $productPrice);
        }

        $totalPriceVatAmount = $this->getTotalPriceVatAmount($totalPriceWithVat, $product->getVatForDomain($domainId));
        $totalPriceWithoutVat = $this->getTotalPriceWithoutVat($totalPriceWithVat, $totalPriceVatAmount);
        $totalPrice = new Price($totalPriceWithoutVat, $totalPriceWithVat);

        return new QuantifiedItemPrice($productPrice, $totalPrice, $product->getVatForDomain($domainId));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int $domainId
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param \App\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[]
     */
    public function calculatePrices(array $quantifiedProducts, int $domainId, ?CustomerUser $customerUser = null, array $promoCodePerProduct = []): array
    {
        $quantifiedItemsPrices = [];
        foreach ($quantifiedProducts as $quantifiedItemIndex => $quantifiedProduct) {
            $promoCode = $promoCodePerProduct[$quantifiedProduct->getProduct()->getId()] ?? null;
            $quantifiedItemsPrices[$quantifiedItemIndex] = $this->calculatePrice($quantifiedProduct, $domainId, $customerUser, $promoCode);
        }

        return $quantifiedItemsPrices;
    }

    /**
     * @param array $quantifiedProducts
     * @param int $domainId
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @return array
     */
    public function calculatePricesWithoutDiscount(array $quantifiedProducts, int $domainId, ?CustomerUser $customerUser = null): array
    {
        $quantifiedItemsPrices = [];
        foreach ($quantifiedProducts as $quantifiedItemIndex => $quantifiedProduct) {
            $quantifiedItemsPrices[$quantifiedItemIndex] = $this->calculatePrice($quantifiedProduct, $domainId, $customerUser)->getTotalPrice();
        }

        return $quantifiedItemsPrices;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int $domainId
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param \App\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[]
     */
    public function calculatePromoCodeApplicablePrices(array $quantifiedProducts, int $domainId, ?CustomerUser $customerUser, array $promoCodePerProduct): array
    {
        $quantifiedItemsPrices = [];
        foreach ($quantifiedProducts as $quantifiedItemIndex => $quantifiedProduct) {
            $promoCode = $promoCodePerProduct[$quantifiedProduct->getProduct()->getId()] ?? null;
            if ($promoCode !== null) {
                $quantifiedItemsPrices[$quantifiedItemIndex] = $this->calculatePrice($quantifiedProduct, $domainId, $customerUser, $promoCode);
            }
        }

        return $quantifiedItemsPrices;
    }
}

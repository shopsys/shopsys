<?php

declare(strict_types=1);

namespace App\Model\Product\Pricing;

use App\Model\Order\Item\QuantifiedItemPrice;
use App\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice as BaseQuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation as BaseQuantifiedProductPriceCalculation;

/**
 * @property \App\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
 * @method __construct(\App\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser, \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation $priceCalculation)
 */
class QuantifiedProductPriceCalculation extends BaseQuantifiedProductPriceCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param int $domainId
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param \App\Model\Order\PromoCode\PromoCode|null $promoCode
     *
     * @return \App\Model\Order\Item\QuantifiedItemPrice
     */
    public function calculatePrice(QuantifiedProduct $quantifiedProduct, int $domainId, ?CustomerUser $customerUser = null, ?PromoCode $promoCode = null): BaseQuantifiedItemPrice
    {
        $product = $quantifiedProduct->getProduct();

        $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCustomerUserAndDomainId(
            $product,
            $domainId,
            $customerUser
        );
        $productHighPrice = $this->productPriceCalculationForCustomerUser->calculateNonSellingPriceForCurrentUserAndDomainId(
            $product,
            $domainId
        );

        if ($promoCode !== null) {
            if ($promoCode->isApplyOnSecondProduct()) {
                $quantity = $quantifiedProduct->getQuantity();
                $productSellingPriceQuantity = $quantity % 2;
                $totalPriceDiscountedProducts = $productHighPrice->getPriceWithVat()->multiply($quantity - $productSellingPriceQuantity);
                $totalPriceProducts = $productPrice->getPriceWithVat()->multiply($productSellingPriceQuantity);

                $totalPriceWithVat = $totalPriceDiscountedProducts->add($totalPriceProducts);
            } else {
                $totalPriceWithVat = $this->getTotalHighPriceWithVat($quantifiedProduct, $productHighPrice);
            }
        } else {
            $totalPriceWithVat = $this->getTotalPriceWithVat($quantifiedProduct, $productPrice);
        }

        $totalPriceVatAmount = $this->getTotalPriceVatAmount($totalPriceWithVat, $product->getVatForDomain($domainId));
        $totalPriceWithoutVat = $this->getTotalPriceWithoutVat($totalPriceWithVat, $totalPriceVatAmount);
        $totalPrice = new Price($totalPriceWithoutVat, $totalPriceWithVat);

        return new QuantifiedItemPrice($productPrice, $totalPrice, $product->getVatForDomain($domainId), $productHighPrice);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $unitPrice
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getTotalHighPriceWithVat(QuantifiedProduct $quantifiedProduct, Price $unitPrice): Money
    {
        return $unitPrice->getPriceWithVat()->multiply($quantifiedProduct->getQuantity());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int $domainId
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param \App\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct
     *
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
}

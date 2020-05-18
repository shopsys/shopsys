<?php

declare(strict_types=1);

namespace App\Model\Product\Pricing;

use App\Model\Order\Item\QuantifiedItemPrice;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice as BaseQuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation as BaseQuantifiedProductPriceCalculation;

/**
 * @property \App\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
 * @method __construct(\App\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser, \Shopsys\FrameworkBundle\Model\Pricing\Rounding $rounding, \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation $priceCalculation)
 * @method \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] calculatePrices(\Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts, int $domainId, \App\Model\Customer\User\CustomerUser|null $customerUser)
 * @method __construct(\App\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser, \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation $priceCalculation)
 */
class QuantifiedProductPriceCalculation extends BaseQuantifiedProductPriceCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param int $domainId
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     *
     * @return \App\Model\Order\Item\QuantifiedItemPrice
     */
    public function calculatePrice(QuantifiedProduct $quantifiedProduct, int $domainId, ?CustomerUser $customerUser = null): BaseQuantifiedItemPrice
    {
        $product = $quantifiedProduct->getProduct();

        $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCustomerUserAndDomainId(
            $product,
            $domainId,
            $customerUser
        );

        $totalPriceWithVat = $this->getTotalPriceWithVat($quantifiedProduct, $productPrice);
        $totalPriceVatAmount = $this->getTotalPriceVatAmount($totalPriceWithVat, $product->getVatForDomain($domainId));
        $totalPriceWithoutVat = $this->getTotalPriceWithoutVat($totalPriceWithVat, $totalPriceVatAmount);
        $totalPrice = new Price($totalPriceWithoutVat, $totalPriceWithVat);

        $productHighPrice = $this->productPriceCalculationForCustomerUser->calculateNonSellingPriceForCurrentUserAndDomainId(
            $product,
            $domainId
        );

        $totalHighPriceWithVat = $this->getTotalHighPriceWithVat($quantifiedProduct, $productHighPrice);
        $totalHighPriceVatAmount = $this->getTotalPriceVatAmount($totalHighPriceWithVat, $product->getVatForDomain($domainId));
        $totalHighPriceWithoutVat = $this->getTotalPriceWithoutVat($totalHighPriceWithVat, $totalHighPriceVatAmount);

        $totalHighPrice = new Price($totalHighPriceWithoutVat, $totalHighPriceWithVat);

        return new QuantifiedItemPrice($productPrice, $totalPrice, $product->getVatForDomain($domainId), $totalHighPrice);
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
}

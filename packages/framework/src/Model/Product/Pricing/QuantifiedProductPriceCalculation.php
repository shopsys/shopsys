<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class QuantifiedProductPriceCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation $priceCalculation
     */
    public function __construct(
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly PriceCalculation $priceCalculation,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice
     */
    public function calculatePrice(QuantifiedProduct $quantifiedProduct, int $domainId, ?CustomerUser $customerUser = null): QuantifiedItemPrice
    {
        $product = $quantifiedProduct->getProduct();

        $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCustomerUserAndDomainId(
            $product,
            $domainId,
            $customerUser,
        );

        $totalPriceWithVat = $this->getTotalPriceWithVat($quantifiedProduct, $productPrice);
        $totalPriceVatAmount = $this->getTotalPriceVatAmount($totalPriceWithVat, $product->getVatForDomain($domainId));
        $priceWithoutVat = $this->getTotalPriceWithoutVat($totalPriceWithVat, $totalPriceVatAmount);

        $totalPrice = new Price($priceWithoutVat, $totalPriceWithVat);

        return new QuantifiedItemPrice($productPrice, $totalPrice, $product->getVatForDomain($domainId));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $totalPriceWithVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $totalPriceVatAmount
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getTotalPriceWithoutVat(Money $totalPriceWithVat, Money $totalPriceVatAmount): Money
    {
        return $totalPriceWithVat->subtract($totalPriceVatAmount);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $unitPrice
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getTotalPriceWithVat(QuantifiedProduct $quantifiedProduct, Price $unitPrice): Money
    {
        return $unitPrice->getPriceWithVat()->multiply($quantifiedProduct->getQuantity());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $totalPriceWithVat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getTotalPriceVatAmount(Money $totalPriceWithVat, Vat $vat): Money
    {
        return $this->priceCalculation->getVatAmountByPriceWithVat($totalPriceWithVat, $vat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[]
     */
    public function calculatePrices(array $quantifiedProducts, int $domainId, ?CustomerUser $customerUser = null): array
    {
        $quantifiedItemsPrices = [];

        foreach ($quantifiedProducts as $quantifiedItemIndex => $quantifiedProduct) {
            $quantifiedItemsPrices[$quantifiedItemIndex] = $this->calculatePrice(
                $quantifiedProduct,
                $domainId,
                $customerUser,
            );
        }

        return $quantifiedItemsPrices;
    }
}

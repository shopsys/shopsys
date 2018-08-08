<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Product\Product;

class QuantifiedProductPriceCalculation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser
     */
    private $productPriceCalculationForUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Rounding
     */
    private $rounding;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct
     */
    private $quantifiedProduct;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    private $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private $productPrice;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation
     */
    private $priceCalculation;

    public function __construct(
        ProductPriceCalculationForUser $productPriceCalculationForUser,
        Rounding $rounding,
        PriceCalculation $priceCalculation
    ) {
        $this->productPriceCalculationForUser = $productPriceCalculationForUser;
        $this->rounding = $rounding;
        $this->priceCalculation = $priceCalculation;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User|null $user
     */
    public function calculatePrice(QuantifiedProduct $quantifiedProduct, $domainId, User $user = null): \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice
    {
        $product = $quantifiedProduct->getProduct();
        if (!$product instanceof Product) {
            $message = 'Object "' . get_class($product) . '" is not valid for QuantifiedProductPriceCalculation.';
            throw new \Shopsys\FrameworkBundle\Model\Order\Item\Exception\InvalidQuantifiedProductException($message);
        }

        $this->quantifiedProduct = $quantifiedProduct;
        $this->product = $product;
        $this->productPrice = $this->productPriceCalculationForUser->calculatePriceForUserAndDomainId(
            $product,
            $domainId,
            $user
        );

        $quantifiedItemPrice = new QuantifiedItemPrice(
            $this->productPrice,
            new Price(
                $this->getTotalPriceWithoutVat(),
                $this->getTotalPriceWithVat()
            ),
            $product->getVat()
        );

        return $quantifiedItemPrice;
    }

    private function getTotalPriceWithoutVat(): string
    {
        return $this->getTotalPriceWithVat() - $this->getTotalPriceVatAmount();
    }

    private function getTotalPriceWithVat(): string
    {
        return $this->productPrice->getPriceWithVat() * $this->quantifiedProduct->getQuantity();
    }

    private function getTotalPriceVatAmount(): string
    {
        $vatPercent = $this->product->getVat()->getPercent();

        return $this->rounding->roundVatAmount(
            $this->getTotalPriceWithVat() * $this->priceCalculation->getVatCoefficientByPercent($vatPercent)
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User|null $user
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[]
     */
    public function calculatePrices(array $quantifiedProducts, $domainId, User $user = null): array
    {
        $quantifiedItemsPrices = [];
        foreach ($quantifiedProducts as $quantifiedItemIndex => $quantifiedProduct) {
            $quantifiedItemsPrices[$quantifiedItemIndex] = $this->calculatePrice($quantifiedProduct, $domainId, $user);
        }

        return $quantifiedItemsPrices;
    }
}

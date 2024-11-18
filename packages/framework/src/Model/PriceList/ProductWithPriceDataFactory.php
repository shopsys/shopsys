<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class ProductWithPriceDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     */
    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly PricingSetting $pricingSetting,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceData
     */
    protected function createInstance(): ProductWithPriceData
    {
        return new ProductWithPriceData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPrice $productWithPrice
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceData
     */
    public function createFromProductWithPrice(
        ProductWithPrice $productWithPrice,
        int $domainId,
    ): ProductWithPriceData {
        $productWithPriceData = $this->createInstance();
        $this->fillFromProductWithPrice($productWithPriceData, $productWithPrice, $domainId);

        return $productWithPriceData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceAmount
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceData
     */
    public function create(Product $product, Money $priceAmount, int $domainId): ProductWithPriceData
    {
        $productWithPriceData = $this->createInstance();
        $productWithPriceData->product = $product;
        $productWithPriceData->priceAmount = $priceAmount;
        $productWithPriceData->basicPrice = $this->getBasicPriceBasedOnPricingSetting(
            $product,
            $domainId,
        );

        return $productWithPriceData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceData $productWithPriceData
     * @param \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPrice $productWithPrice
     * @param int $domainId
     */
    protected function fillFromProductWithPrice(
        ProductWithPriceData $productWithPriceData,
        ProductWithPrice $productWithPrice,
        int $domainId,
    ): void {
        $productWithPriceData->product = $productWithPrice->getProduct();
        $productWithPriceData->priceAmount = $productWithPrice->getPriceAmount();
        $productWithPriceData->basicPrice = $this->getBasicPriceBasedOnPricingSetting(
            $productWithPrice->getProduct(),
            $domainId,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getBasicPriceBasedOnPricingSetting(Product $product, int $domainId): Money
    {
        $basicPrice = $this->productFacade->getProductSellingPriceForDefaultPricingGroup(
            $product,
            $domainId,
        );

        if ($this->pricingSetting->getInputPriceType() === PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT) {
            return $basicPrice->getPriceWithoutVat();
        }

        return $basicPrice->getPriceWithVat();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPrice[] $productsWithPrices
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceData[]
     */
    public function createFromProductsWithPrices(array $productsWithPrices, int $domainId): array
    {
        $productsWithPricesData = [];

        foreach ($productsWithPrices as $productWithPrice) {
            $productsWithPricesData[] = $this->createFromProductWithPrice($productWithPrice, $domainId);
        }

        return $productsWithPricesData;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class PriceListProductPriceDataFactory
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
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPriceData
     */
    protected function createInstance(): PriceListProductPriceData
    {
        return new PriceListProductPriceData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPrice $priceListProductPrice
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPriceData
     */
    public function createFromPriceListProductPrice(
        PriceListProductPrice $priceListProductPrice,
        int $domainId,
    ): PriceListProductPriceData {
        $priceListProductPriceData = $this->createInstance();
        $this->fillFromPriceListProductPrice($priceListProductPriceData, $priceListProductPrice, $domainId);

        return $priceListProductPriceData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceAmount
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPriceData
     */
    public function create(Product $product, Money $priceAmount, int $domainId): PriceListProductPriceData
    {
        $priceListProductPrice = $this->createInstance();
        $priceListProductPrice->product = $product;
        $priceListProductPrice->priceAmount = $priceAmount;
        $priceListProductPrice->basicPrice = $this->getBasicPriceBasedOnPricingSetting(
            $product,
            $domainId,
        );

        return $priceListProductPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPriceData $priceListProductPriceData
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPrice $priceListProductPrice
     * @param int $domainId
     */
    protected function fillFromPriceListProductPrice(
        PriceListProductPriceData $priceListProductPriceData,
        PriceListProductPrice $priceListProductPrice,
        int $domainId,
    ): void {
        $priceListProductPriceData->product = $priceListProductPrice->getProduct();
        $priceListProductPriceData->priceAmount = $priceListProductPrice->getPriceAmount();
        $priceListProductPriceData->basicPrice = $this->getBasicPriceBasedOnPricingSetting(
            $priceListProductPrice->getProduct(),
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
        $basicPrice = $this->productFacade->getProductPriceForDefaultPricingGroup(
            $product,
            $domainId,
        );

        if ($this->pricingSetting->getInputPriceType() === PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT) {
            return $basicPrice->getPriceWithoutVat();
        }

        return $basicPrice->getPriceWithVat();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPrice[] $priceListProductPrices
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPriceData[]
     */
    public function createFromPriceListProductPrices(array $priceListProductPrices, int $domainId): array
    {
        $priceListProductPricesData = [];

        foreach ($priceListProductPrices as $priceListProductPrice) {
            $priceListProductPricesData[] = $this->createFromPriceListProductPrice($priceListProductPrice, $domainId);
        }

        return $priceListProductPricesData;
    }
}

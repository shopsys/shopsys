<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class PriceListProductPriceDataFactory
{
    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly PricingSetting $pricingSetting,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    protected function createInstance(): PriceListProductPriceData
    {
        return new PriceListProductPriceData();
    }

    public function createFromPriceListProductPrice(
        PriceListProductPrice $priceListProductPrice,
        int $domainId,
    ): PriceListProductPriceData {
        $priceListProductPriceData = $this->createInstance();
        $this->fillFromPriceListProductPrice($priceListProductPriceData, $priceListProductPrice, $domainId);

        return $priceListProductPriceData;
    }

    public function create(Product $product, Money $priceAmount, int $domainId): PriceListProductPriceData
    {
        $priceListProductPrice = $this->createInstance();
        $priceListProductPrice->product = $product;
        $priceListProductPrice->priceAmount = $priceAmount;
        $priceListProductPrice->currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $priceListProductPrice->basicPrice = $this->getBasicPriceBasedOnPricingSetting(
            $product,
            $domainId,
        );

        return $priceListProductPrice;
    }

    protected function fillFromPriceListProductPrice(
        PriceListProductPriceData $priceListProductPriceData,
        PriceListProductPrice $priceListProductPrice,
        int $domainId,
    ): void {
        $priceListProductPriceData->product = $priceListProductPrice->getProduct();
        $priceListProductPriceData->priceAmount = $priceListProductPrice->getPriceAmount();
        $priceListProductPriceData->currency = $priceListProductPrice->getCurrency();
        $priceListProductPriceData->basicPrice = $this->getBasicPriceBasedOnPricingSetting(
            $priceListProductPrice->getProduct(),
            $domainId,
        );
    }

    protected function getBasicPriceBasedOnPricingSetting(Product $product, int $domainId): Money
    {
        $basicPrice = $this->productFacade->getProductPriceForDefaultPricingGroup(
            $product,
            $domainId,
        );

        if ($this->pricingSetting->getInputPriceType() === PricingSetting::PRICE_TYPE_WITHOUT_VAT) {
            return $basicPrice->getPrice()->getPriceWithoutVat();
        }

        return $basicPrice->getPrice()->getPriceWithVat();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPrice[] $priceListProductPrices
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

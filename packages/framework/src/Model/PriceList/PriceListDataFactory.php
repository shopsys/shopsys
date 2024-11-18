<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

class PriceListDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceDataFactory $productWithPriceDataFactory
     */
    public function __construct(
        protected readonly ProductWithPriceDataFactory $productWithPriceDataFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceListData
     */
    protected function createInstance(): PriceListData
    {
        return new PriceListData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceListData
     */
    public function create(): PriceListData
    {
        $priceListData = $this->createInstance();
        $this->fillNew($priceListData);

        return $priceListData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListData $priceListData
     */
    protected function fillNew(PriceListData $priceListData): void
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceList $priceList
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceListData
     */
    public function createFromPriceList(PriceList $priceList): PriceListData
    {
        $priceListData = $this->createInstance();
        $this->fillFromPriceList($priceListData, $priceList);

        return $priceListData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListData $priceListData
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceList $priceList
     */
    protected function fillFromPriceList(PriceListData $priceListData, PriceList $priceList): void
    {
        $priceListData->name = $priceList->getName();
        $priceListData->domainId = $priceList->getDomainId();
        $priceListData->validFrom = $priceList->getValidFrom();
        $priceListData->validTo = $priceList->getValidTo();
        $priceListData->productsWithPrices = $this->productWithPriceDataFactory->createFromProductsWithPrices(
            $priceList->getProductsWithPrices(),
            $priceList->getDomainId(),
        );
    }
}

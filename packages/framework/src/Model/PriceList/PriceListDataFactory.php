<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

class PriceListDataFactory
{
    public function __construct(
        protected readonly PriceListProductPriceDataFactory $priceListProductPriceDataFactory,
    ) {
    }

    protected function createInstance(): PriceListData
    {
        return new PriceListData();
    }

    public function create(): PriceListData
    {
        $priceListData = $this->createInstance();
        $this->fillNew($priceListData);

        return $priceListData;
    }

    protected function fillNew(PriceListData $priceListData): void
    {
    }

    public function createFromPriceList(PriceList $priceList): PriceListData
    {
        $priceListData = $this->createInstance();
        $this->fillFromPriceList($priceListData, $priceList);

        return $priceListData;
    }

    protected function fillFromPriceList(PriceListData $priceListData, PriceList $priceList): void
    {
        $priceListData->id = $priceList->getId();
        $priceListData->name = $priceList->getName();
        $priceListData->domainId = $priceList->getDomainId();
        $priceListData->validFrom = $priceList->getValidFrom();
        $priceListData->validTo = $priceList->getValidTo();
        $priceListData->priceListProductPricesData = $this->priceListProductPriceDataFactory->createFromPriceListProductPrices(
            $priceList->getPriceListProductPrices(),
            $priceList->getDomainId(),
        );
    }
}

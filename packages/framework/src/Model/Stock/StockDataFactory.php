<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class StockDataFactory
{
    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

    protected function createInstance(): StockData
    {
        return new StockData();
    }

    public function create(): StockData
    {
        $stockData = $this->createInstance();
        $this->fillNew($stockData);

        return $stockData;
    }

    public function createFromStock(Stock $stock): StockData
    {
        $stockData = $this->createInstance();
        $this->fillFromStock($stockData, $stock);

        return $stockData;
    }

    protected function fillFromStock(StockData $stockData, Stock $stock): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $stockData->isEnabledByDomain[$domainId] = $stock->isEnabled($domainId);
        }

        $stockData->name = $stock->getName();
        $stockData->isDefault = $stock->isDefault();
        $stockData->externalId = $stock->getExternalId();
        $stockData->note = $stock->getNote();
    }

    protected function fillNew(StockData $stockData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $stockData->isEnabledByDomain[$domainId] = false;
        }
    }
}

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

    public function create(bool $withFirstDefaults = false): StockData
    {
        $stockData = $this->createInstance();
        $this->fillNew($stockData, $withFirstDefaults);

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
            $stockData->isDefaultByDomain[$domainId] = $stock->isDefault($domainId);
        }

        $stockData->name = $stock->getName();
        $stockData->externalId = $stock->getExternalId();
        $stockData->note = $stock->getNote();
    }

    protected function fillNew(StockData $stockData, bool $withFirstDefaults = false): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $stockData->isEnabledByDomain[$domainId] = $withFirstDefaults;
            $stockData->isDefaultByDomain[$domainId] = $withFirstDefaults;
        }
    }
}

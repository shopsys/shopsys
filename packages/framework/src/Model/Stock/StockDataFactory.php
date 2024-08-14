<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class StockDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\StockData
     */
    protected function createInstance(): StockData
    {
        return new StockData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\StockData
     */
    public function create(): StockData
    {
        $stockData = $this->createInstance();
        $this->fillNew($stockData);

        return $stockData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\Stock $stock
     * @return \Shopsys\FrameworkBundle\Model\Stock\StockData
     */
    public function createFromStock(Stock $stock): StockData
    {
        $stockData = $this->createInstance();
        $this->fillFromStock($stockData, $stock);

        return $stockData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockData $stockData
     * @param \Shopsys\FrameworkBundle\Model\Stock\Stock $stock
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockData $stockData
     */
    protected function fillNew(StockData $stockData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $stockData->isEnabledByDomain[$domainId] = false;
        }
    }
}

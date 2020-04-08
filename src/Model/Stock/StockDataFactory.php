<?php

declare(strict_types=1);

namespace App\Model\Stock;

class StockDataFactory
{
    /**
     * @return \App\Model\Stock\StockData
     */
    public function create(): StockData
    {
        return new StockData();
    }

    /**
     * @param \App\Model\Stock\Stock $stock
     * @return \App\Model\Stock\StockData
     */
    public function createFromStock(Stock $stock): StockData
    {
        $stockData = new StockData();
        $stockData->name = $stock->getName();
        $stockData->domainId = $stock->getDomainId();
        $stockData->centralStock = $stock->isCentralStock();
        $stockData->externalId = $stock->getExternalId();
        $stockData->street = $stock->getStreet();
        $stockData->city = $stock->getCity();
        return $stockData;
    }
}

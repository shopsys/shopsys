<?php

declare(strict_types=1);


namespace App\Model\Stock;


interface StockDataFactoryInterface
{
    /**
     * @return \App\Model\Stock\StockData
     */
    public function create(): StockData;

    /**
     * @param \App\Model\Stock\Stock $stock
     * @return \App\Model\Stock\StockData
     */
    public function createFromStock(Stock $stock): StockData;
}
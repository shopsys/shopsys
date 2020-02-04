<?php

declare(strict_types=1);

namespace App\Model\Stock;

interface ProductStockDataFactoryInterface
{
    /**
     * @param \App\Model\Stock\Stock $stock
     * @return \App\Model\Stock\ProductStockData
     */
    public function createFromStock(Stock $stock): ProductStockData;

    /**
     * @param \App\Model\Stock\ProductStock $productStock
     * @return \App\Model\Stock\ProductStockData
     */
    public function createFromProductStock(ProductStock $productStock): ProductStockData;
}

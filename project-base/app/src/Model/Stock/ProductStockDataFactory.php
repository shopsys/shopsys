<?php

declare(strict_types=1);

namespace App\Model\Stock;

class ProductStockDataFactory
{
    /**
     * @return \App\Model\Stock\ProductStockData
     */
    private function create(): \App\Model\Stock\ProductStockData
    {
        return new ProductStockData();
    }

    /**
     * @param \App\Model\Stock\Stock $stock
     * @return \App\Model\Stock\ProductStockData
     */
    public function createFromStock(Stock $stock): ProductStockData
    {
        $productStockData = $this->create();
        $productStockData->name = $stock->getName();
        $productStockData->stockId = $stock->getId();

        return $productStockData;
    }

    /**
     * @param \App\Model\Stock\ProductStock $productStock
     * @return \App\Model\Stock\ProductStockData
     */
    public function createFromProductStock(ProductStock $productStock): ProductStockData
    {
        $productStockData = $this->create();
        $productStockData->name = $productStock->getStock()->getName();
        $productStockData->stockId = $productStock->getStock()->getId();
        $productStockData->productQuantity = $productStock->getProductQuantity();

        return $productStockData;
    }
}

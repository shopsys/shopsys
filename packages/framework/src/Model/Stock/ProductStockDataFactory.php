<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

class ProductStockDataFactory
{
    protected function create(): ProductStockData
    {
        return new ProductStockData();
    }

    public function createFromStock(Stock $stock): ProductStockData
    {
        $productStockData = $this->create();
        $productStockData->name = $stock->getName();
        $productStockData->stockId = $stock->getId();

        return $productStockData;
    }

    public function createFromProductStock(ProductStock $productStock): ProductStockData
    {
        $productStockData = $this->create();
        $productStockData->name = $productStock->getStock()->getName();
        $productStockData->stockId = $productStock->getStock()->getId();
        $productStockData->productQuantity = $productStock->getProductQuantity();

        return $productStockData;
    }
}

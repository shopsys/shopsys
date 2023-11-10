<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

class ProductStockDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\ProductStockData
     */
    protected function create(): ProductStockData
    {
        return new ProductStockData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\Stock $stock
     * @return \Shopsys\FrameworkBundle\Model\Stock\ProductStockData
     */
    public function createFromStock(Stock $stock): ProductStockData
    {
        $productStockData = $this->create();
        $productStockData->name = $stock->getName();
        $productStockData->stockId = $stock->getId();

        return $productStockData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\ProductStock $productStock
     * @return \Shopsys\FrameworkBundle\Model\Stock\ProductStockData
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

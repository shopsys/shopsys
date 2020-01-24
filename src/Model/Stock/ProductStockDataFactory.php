<?php

declare(strict_types=1);

namespace App\Model\Stock;

class ProductStockDataFactory implements ProductStockDataFactoryInterface
{
    /**
     * @var \App\Model\Stock\StockFacade
     */
    private $stockFacade;

    /**
     * @param \App\Model\Stock\StockFacade $stockFacade
     */
    public function __construct(StockFacade $stockFacade)
    {
        $this->stockFacade = $stockFacade;
    }

    /**
     * @return \App\Model\Stock\ProductStockData
     */
    public function create()
    {
        return new ProductStockData();
    }

    /**
     * @param \App\Model\Stock\Stock $stock
     * @return \App\Model\Stock\ProductStockData
     */
    public function createFromStock(Stock $stock)
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
    public function createFromProductStock(ProductStock $productStock)
    {
        $productStockData = $this->create();
        $productStockData->name = $productStock->getStock()->getName();
        $productStockData->stockId = $productStock->getStock()->getId();
        $productStockData->productQuantity = $productStock->getProductQuantity();
        return $productStockData;
    }

    /**
     * @param \App\Model\Stock\ProductStockData $productStockData
     */
    public function initStockByProductStockData(ProductStockData $productStockData)
    {
        $productStockData->stock = $this->stockFacade->getById($productStockData->stockId);
    }
}

<?php

declare(strict_types=1);

namespace App\Model\Stock;

class StockProductDataFactory implements StockProductDataFactoryInterface
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
     * @return \App\Model\Stock\StockProductData
     */
    public function create()
    {
        return new StockProductData();
    }

    /**
     * @param \App\Model\Stock\Stock $stock
     * @return \App\Model\Stock\StockProductData
     */
    public function createFromStock(Stock $stock)
    {
        $stockProductData = $this->create();
        $stockProductData->name = $stock->getName();
        $stockProductData->stockId = $stock->getId();
        return $stockProductData;
    }

    /**
     * @param \App\Model\Stock\StockProduct $stockProduct
     * @return \App\Model\Stock\StockProductData
     */
    public function createFromStockProduct(StockProduct $stockProduct)
    {
        $stockProductData = $this->create();
        $stockProductData->name = $stockProduct->getStock()->getName();
        $stockProductData->stockId = $stockProduct->getStock()->getId();
        $stockProductData->productQuantity = $stockProduct->getProductQuantity();
        return $stockProductData;
    }

    /**
     * @param \App\Model\Stock\StockProductData $stockProductData
     */
    public function initStockByStockProductData(StockProductData $stockProductData)
    {
        $stockProductData->stock = $this->stockFacade->getById($stockProductData->stockId);
    }
}

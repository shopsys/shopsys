<?php

declare(strict_types=1);

namespace App\Model\Stock;

interface StockProductDataFactoryInterface
{
    /**
     * @return \App\Model\Stock\StockProductData
     */
    public function create();

    /**
     * @param \App\Model\Stock\Stock $stock
     * @return \App\Model\Stock\StockProductData
     */
    public function createFromStock(Stock $stock);

    /**
     * @param \App\Model\Stock\StockProduct $stockProduct
     * @return \App\Model\Stock\StockProductData
     */
    public function createFromStockProduct(StockProduct $stockProduct);

    /**
     * @param \App\Model\Stock\StockProductData $stockProductData
     */
    public function initStockByStockProductData(StockProductData $stockProductData);
}

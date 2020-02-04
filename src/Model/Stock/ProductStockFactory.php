<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Model\Product\Product;

class ProductStockFactory
{
    /**
     * @param \App\Model\Stock\Stock $stock
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\ProductStock
     */
    public function createProductStock(Stock $stock, Product $product): ProductStock
    {
        return new ProductStock($stock, $product);
    }
}

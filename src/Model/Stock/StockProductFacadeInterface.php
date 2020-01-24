<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Model\Product\Product;

interface StockProductFacadeInterface
{
    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\StockProduct[]
     */
    public function getStockProductsByProduct(Product $product): array;
}

<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Model\Product\Product;

interface ProductStockFacadeInterface
{
    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\ProductStock[]
     */
    public function getProductsStockByProduct(Product $product): array;
}

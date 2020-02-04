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
    public function getProductStocksByProduct(Product $product): array;

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Stock\Stock $stock
     * @param int $productQuantity
     */
    public function setProductStockQuantity(Product $product, Stock $stock, int $productQuantity);
}

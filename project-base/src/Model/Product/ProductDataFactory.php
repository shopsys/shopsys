<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory as BaseProductDataFactory;

/**
 * @method \App\Model\Product\ProductData create()
 * @method \App\Model\Product\ProductData createFromProduct(\App\Model\Product\Product $product)
 */
class ProductDataFactory extends BaseProductDataFactory
{
    /**
     * @return \App\Model\Product\ProductData
     */
    protected function createInstance(): BaseProductData
    {
        return new ProductData();
    }
}

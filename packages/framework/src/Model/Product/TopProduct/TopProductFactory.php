<?php

namespace Shopsys\FrameworkBundle\Model\Product\TopProduct;

use Shopsys\FrameworkBundle\Model\Product\Product;

class TopProductFactory implements TopProductFactoryInterface
{
    public function create(
        Product $product,
        int $domainId,
        int $position
    ): TopProduct {
        return new TopProduct($product, $domainId, $position);
    }
}

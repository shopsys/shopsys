<?php

namespace Shopsys\FrameworkBundle\Model\Product\TopProduct;

use Shopsys\FrameworkBundle\Model\Product\Product;

interface TopProductFactoryInterface
{
    public function create(
        Product $product,
        int $domainId,
        int $position
    ): TopProduct;
}

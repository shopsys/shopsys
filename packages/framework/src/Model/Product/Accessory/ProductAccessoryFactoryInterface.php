<?php

namespace Shopsys\FrameworkBundle\Model\Product\Accessory;

use Shopsys\FrameworkBundle\Model\Product\Product;

interface ProductAccessoryFactoryInterface
{

    public function create(
        Product $product,
        Product $accessory,
        int $position
    ): ProductAccessory;
}

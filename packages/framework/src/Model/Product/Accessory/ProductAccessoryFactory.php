<?php

namespace Shopsys\FrameworkBundle\Model\Product\Accessory;

use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductAccessoryFactory implements ProductAccessoryFactoryInterface
{
    public function create(
        Product $product,
        Product $accessory,
        int $position
    ): ProductAccessory {
        return new ProductAccessory($product, $accessory, $position);
    }
}

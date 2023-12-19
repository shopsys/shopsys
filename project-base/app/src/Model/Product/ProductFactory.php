<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductFactory as BaseProductFactory;

/**
 * @method \App\Model\Product\Product create(\App\Model\Product\ProductData $data)
 * @method \App\Model\Product\Product createMainVariant(\App\Model\Product\ProductData $data, \App\Model\Product\Product $mainProduct, \App\Model\Product\Product[] $variants)
 */
class ProductFactory extends BaseProductFactory
{
}

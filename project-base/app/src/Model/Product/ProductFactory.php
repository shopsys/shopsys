<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFactory as BaseProductFactory;

/**
 * @property \App\Model\Product\Availability\ProductAvailabilityCalculation $productAvailabilityCalculation
 * @method __construct(\Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver, \App\Model\Product\Availability\ProductAvailabilityCalculation $productAvailabilityCalculation)
 * @method \App\Model\Product\Product create(\App\Model\Product\ProductData $data)
 * @method \App\Model\Product\Product createMainVariant(\App\Model\Product\ProductData $data, \App\Model\Product\Product $mainProduct, \App\Model\Product\Product[] $variants)
 */
class ProductFactory extends BaseProductFactory
{
    /**
     * @param \App\Model\Product\Product $product
     */
    protected function setCalculatedAvailabilityIfMissing(Product $product)
    {
        //remove dependency on CalculatedAvailability
    }
}

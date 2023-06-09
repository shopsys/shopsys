<?php

namespace Shopsys\FrameworkBundle\Model\Product\Accessory;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductAccessoryFactory implements ProductAccessoryFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $accessory
     * @param int $position
     * @return \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessory
     */
    public function create(
        Product $product,
        Product $accessory,
        int $position
    ): ProductAccessory {
        $classData = $this->entityNameResolver->resolve(ProductAccessory::class);

        return new $classData($product, $accessory, $position);
    }
}

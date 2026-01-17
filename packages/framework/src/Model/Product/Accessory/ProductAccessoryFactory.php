<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Accessory;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductAccessoryFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(
        Product $product,
        Product $accessory,
        int $position,
    ): ProductAccessory {
        $entityClassName = $this->entityNameResolver->resolve(ProductAccessory::class);

        return new $entityClassName($product, $accessory, $position);
    }
}

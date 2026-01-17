<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductStockFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(Stock $stock, Product $product): ProductStock
    {
        $entityClassName = $this->entityNameResolver->resolve(ProductStock::class);

        return new $entityClassName($stock, $product);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\TopProduct;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;

class TopProductFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(
        Product $product,
        int $domainId,
        int $position,
    ): TopProduct {
        $entityClassName = $this->entityNameResolver->resolve(TopProduct::class);

        return new $entityClassName($product, $domainId, $position);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\TopProduct;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;

class TopProductFactory implements TopProductFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param int $position
     * @return \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProduct
     */
    public function create(
        Product $product,
        int $domainId,
        int $position,
    ): TopProduct {
        $entityClassName = $this->entityNameResolver->resolve(TopProduct::class);

        return new $entityClassName($product, $domainId, $position);
    }
}

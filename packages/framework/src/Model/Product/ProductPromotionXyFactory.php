<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ProductPromotionXyFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(ProductPromotionXyData $productPromotionXyData): ProductPromotionXy
    {
        $entityClassName = $this->entityNameResolver->resolve(ProductPromotionXy::class);

        return new $entityClassName($productPromotionXyData);
    }
}

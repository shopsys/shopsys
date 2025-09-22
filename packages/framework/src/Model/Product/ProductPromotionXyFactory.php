<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ProductPromotionXyFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyData $productPromotionXyData
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy
     */
    public function create(ProductPromotionXyData $productPromotionXyData): ProductPromotionXy
    {
        $entityClassName = $this->entityNameResolver->resolve(ProductPromotionXy::class);

        return new $entityClassName($productPromotionXyData);
    }
}

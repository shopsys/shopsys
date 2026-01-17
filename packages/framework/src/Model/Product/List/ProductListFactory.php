<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ProductListFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(ProductListData $productListData): ProductList
    {
        $productListClassName = $this->entityNameResolver->resolve(ProductList::class);

        return new $productListClassName($productListData);
    }
}

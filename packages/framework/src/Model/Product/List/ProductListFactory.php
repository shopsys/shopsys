<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ProductListFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListData $productListData
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductList
     */
    public function create(ProductListData $productListData): ProductList
    {
        $productListClassName = $this->entityNameResolver->resolve(ProductList::class);

        return new $productListClassName($productListData);
    }
}

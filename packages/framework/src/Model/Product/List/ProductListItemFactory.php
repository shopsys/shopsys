<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductListItemFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(ProductList $productList, Product $product): ProductListItem
    {
        $productListClassName = $this->entityNameResolver->resolve(ProductListItem::class);

        return new $productListClassName($productList, $product);
    }
}

<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Category\Category;

interface ProductCategoryDomainFactoryInterface
{
    public function create(
        Product $product,
        Category $category,
        int $domainId
    ): ProductCategoryDomain;
}

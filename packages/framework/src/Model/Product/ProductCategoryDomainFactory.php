<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Category\Category;

class ProductCategoryDomainFactory implements ProductCategoryDomainFactoryInterface
{

    public function create(
        Product $product,
        Category $category,
        int $domainId
    ): ProductCategoryDomain {
        return new ProductCategoryDomain($product, $category, $domainId);
    }
}

<?php

namespace Shopsys\FrameworkBundle\Model\Product\BestsellingProduct;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Product;

interface ManualBestsellingProductFactoryInterface
{
    public function create(
        int $domainId,
        Category $category,
        Product $product,
        int $position
    ): ManualBestsellingProduct;
}

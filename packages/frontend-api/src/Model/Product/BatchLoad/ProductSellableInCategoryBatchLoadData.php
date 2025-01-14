<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\BatchLoad;

use Shopsys\FrameworkBundle\Model\Category\Category;

class ProductSellableInCategoryBatchLoadData
{
    /**
     * @param int[] $productIds
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     */
    public function __construct(
        public readonly array $productIds,
        public readonly Category $category,
    ) {
    }
}

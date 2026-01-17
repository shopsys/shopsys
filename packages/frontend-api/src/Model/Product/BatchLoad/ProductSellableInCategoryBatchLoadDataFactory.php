<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\BatchLoad;

use Shopsys\FrameworkBundle\Model\Category\Category;

class ProductSellableInCategoryBatchLoadDataFactory
{
    /**
     * @param int[] $productIds
     */
    public function create(array $productIds, Category $category): ProductSellableInCategoryBatchLoadData
    {
        return new ProductSellableInCategoryBatchLoadData($productIds, $category);
    }
}

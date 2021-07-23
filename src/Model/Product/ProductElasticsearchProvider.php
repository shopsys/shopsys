<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider as BaseProductElasticsearchProvider;

class ProductElasticsearchProvider extends BaseProductElasticsearchProvider
{
    /**
     * @param array $productIds
     * @return int[]
     */
    public function getVisibleProductsArrayByIds(array $productIds): array
    {
        return $this->productElasticsearchRepository->getProductsByFilterQuery(
            $this->filterQueryFactory->createVisibleProductsByProductIdsFilter($productIds)
        );
    }
}

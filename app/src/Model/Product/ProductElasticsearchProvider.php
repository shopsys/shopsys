<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider as BaseProductElasticsearchProvider;

/**
 * @property \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
 * @property \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
 */
class ProductElasticsearchProvider extends BaseProductElasticsearchProvider
{
    /**
     * @param int[][] $productsIds
     * @return array
     */
    public function getBatchedVisibleByProductIds(array $productsIds): array
    {
        $filterQueries = [];
        foreach ($productsIds as $productIds) {
            $filterQueries[] = $this->filterQueryFactory->createVisibleProductsByProductIdsFilter($productIds);
        }

        return $this->productElasticsearchRepository->getBatchedProductsByFilterQueries($filterQueries);
    }
}

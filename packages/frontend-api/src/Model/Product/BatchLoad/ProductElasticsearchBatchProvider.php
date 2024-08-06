<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\BatchLoad;

use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrontendApiBundle\Model\Product\ProductFrontendLimitProvider;

class ProductElasticsearchBatchProvider
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductElasticsearchBatchRepository $productElasticsearchBatchRepository
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFrontendLimitProvider $productFrontendLimitProvider
     */
    public function __construct(
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly ProductElasticsearchBatchRepository $productElasticsearchBatchRepository,
        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
    ) {
    }

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

        return $this->productElasticsearchBatchRepository->getBatchedProductsAndTotalsByFilterQueries($filterQueries);
    }

    /**
     * @param int[][] $productsIds
     * @return array
     */
    public function getBatchedSellableByProductIds(array $productsIds): array
    {
        $filterQueries = [];

        foreach ($productsIds as $productIds) {
            $filterQueries[] = $this->filterQueryFactory->createSellableProductsByProductIdsFilter($productIds, $this->productFrontendLimitProvider->getProductsFrontendLimit());
        }

        return $this->productElasticsearchBatchRepository->getBatchedProductsAndTotalsByFilterQueries($filterQueries);
    }
}

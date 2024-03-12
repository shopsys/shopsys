<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\BatchLoad;

use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;

class ProductElasticsearchBatchProvider
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductElasticsearchBatchRepository $productElasticsearchBatchRepository
     */
    public function __construct(
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly ProductElasticsearchBatchRepository $productElasticsearchBatchRepository,
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
            $filterQueries[] = $this->filterQueryFactory->createSellableProductsByProductIdsFilter($productIds);
        }

        return $this->productElasticsearchBatchRepository->getBatchedProductsAndTotalsByFilterQueries($filterQueries);
    }

    /**
     * @param array $productIds
     * @param int $domainId
     * @return array<int, bool>
     */
    public function getBatchExistsByProductIds(array $productIds, int $domainId): array
    {
        $filterQueries = [];
        foreach ($productIds as $productId) {
            $filterQueries[$productId] = $this->filterQueryFactory->createExistsProductFilterQuery($productId, $domainId);
        }
d($filterQueries);
        $batchExists = [];
        foreach($this->productElasticsearchBatchRepository->getBatchedTotalsByFilterQueries($filterQueries, $domainId) as $key => $total) {
            $batchExists[$key] = $total > 0;
        }

        return $batchExists;
    }
}

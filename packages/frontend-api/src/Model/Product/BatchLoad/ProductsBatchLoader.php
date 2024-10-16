<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\BatchLoad;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;

class ProductsBatchLoader
{
    /**
     * @var array<string, int>
     */
    protected static array $totalsIndexedByBatchLoadDataId;

    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductElasticsearchBatchProvider $productElasticsearchBatchProvider
     */
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly ProductElasticsearchBatchProvider $productElasticsearchBatchProvider,
    ) {
    }

    /**
     * @param int[][] $productsIds
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadVisibleByIds(array $productsIds): Promise
    {
        return $this->promiseAdapter->all($this->productElasticsearchBatchProvider->getBatchedVisibleByProductIds($productsIds)[ProductElasticsearchBatchRepository::PRODUCTS_KEY]);
    }

    /**
     * @param int[][] $productsIds
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadVisibleAndSortedByIds(array $productsIds): Promise
    {
        $products = $this->productElasticsearchBatchProvider->getBatchedVisibleByProductIds($productsIds)[ProductElasticsearchBatchRepository::PRODUCTS_KEY];
        $sortedProducts = [];

        foreach ($products as $index => $result) {
            $sortedProducts[] = $this->sortByOriginalArray($result, $productsIds[$index]);
        }

        return $this->promiseAdapter->all($sortedProducts);
    }

    /**
     * @param int[][] $productsIds
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadSellableByIds(array $productsIds): Promise
    {
        return $this->promiseAdapter->all($this->productElasticsearchBatchProvider->getBatchedSellableByProductIds($productsIds)[ProductElasticsearchBatchRepository::PRODUCTS_KEY]);
    }

    /**
     * @param array $productsIds
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadSellableCountByIds(array $productsIds): Promise
    {
        return $this->promiseAdapter->all($this->productElasticsearchBatchProvider->getBatchedSellableByProductIds($productsIds)[ProductElasticsearchBatchRepository::TOTALS_KEY]);
    }

    /**
     * @param string $batchLoadDataId
     * @return int
     */
    public static function getTotalByBatchLoadDataId(string $batchLoadDataId): int
    {
        return self::$totalsIndexedByBatchLoadDataId[$batchLoadDataId] ?? 0;
    }

    /**
     * @param array $arrayForSorting
     * @param array $originalArray
     * @return array
     */
    protected function sortByOriginalArray(array $arrayForSorting, array $originalArray): array
    {
        $sortedItems = [];

        foreach ($arrayForSorting as $item) {
            $originalIndex = array_search($item['id'], $originalArray, true);

            if ($originalIndex !== false) {
                $sortedItems[$originalIndex] = $item;
            }
        }
        ksort($sortedItems);

        return $sortedItems;
    }
}

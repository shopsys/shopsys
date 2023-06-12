<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product;

use App\Model\Product\ProductElasticsearchProvider;
use App\Model\Product\Search\ProductElasticsearchRepository;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;

class ProductsBatchLoader
{
    /**
     * @var array<string, int>
     */
    private static array $totalsIndexedByBatchLoadDataId;

    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \App\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     */
    public function __construct(
        private PromiseAdapter $promiseAdapter,
        private ProductElasticsearchProvider $productElasticsearchProvider,
    ) {
    }

    /**
     * @param int[][] $productsIds
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadVisibleByIds(array $productsIds): Promise
    {
        return $this->promiseAdapter->all($this->productElasticsearchProvider->getBatchedVisibleByProductIds($productsIds)[ProductElasticsearchRepository::PRODUCTS_KEY]);
    }

    /**
     * @param int[][] $productsIds
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadVisibleAndSortedByIds(array $productsIds): Promise
    {
        $products = $this->productElasticsearchProvider->getBatchedVisibleByProductIds($productsIds)[ProductElasticsearchRepository::PRODUCTS_KEY];
        $sortedProducts = [];
        foreach ($products as $index => $result) {
            $sortedProducts[] = $this->sortByOriginalArray($result, $productsIds[$index]);
        }

        return $this->promiseAdapter->all($sortedProducts);
    }

    /**
     * @param array $arrayForSorting
     * @param array $originalArray
     * @return array
     */
    private function sortByOriginalArray(array $arrayForSorting, array $originalArray): array
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

    /**
     * @param int[][] $productsIds
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadSellableByIds(array $productsIds): Promise
    {
        return $this->promiseAdapter->all($this->productElasticsearchProvider->getBatchedSellableByProductIds($productsIds)[ProductElasticsearchRepository::PRODUCTS_KEY]);
    }

    /**
     * @param \App\FrontendApi\Model\Product\BatchLoad\ProductBatchLoadByEntityData[] $productBatchLoadByEntitiesData
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByEntities(array $productBatchLoadByEntitiesData): Promise
    {
        $batchedByEntities = $this->productElasticsearchProvider->getBatchedByEntities($productBatchLoadByEntitiesData);
        self::$totalsIndexedByBatchLoadDataId = $batchedByEntities[ProductElasticsearchRepository::TOTALS_KEY];

        $result = [];
        foreach ($productBatchLoadByEntitiesData as $productBatchLoadByEntityData) {
            $result[] = $batchedByEntities[ProductElasticsearchRepository::PRODUCTS_KEY][$productBatchLoadByEntityData->getId()];
        }

        return $this->promiseAdapter->all($result);
    }

    /**
     * @param string $batchLoadDataId
     * @return int
     */
    public static function getTotalByBatchLoadDataId(string $batchLoadDataId): int
    {
        return self::$totalsIndexedByBatchLoadDataId[$batchLoadDataId] ?? 0;
    }
}

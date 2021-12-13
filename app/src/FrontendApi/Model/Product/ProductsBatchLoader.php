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
     * @var \GraphQL\Executor\Promise\PromiseAdapter
     */
    private PromiseAdapter $promiseAdapter;

    /**
     * @var \App\Model\Product\ProductElasticsearchProvider
     */
    private ProductElasticsearchProvider $productElasticsearchProvider;

    /**
     * @var int[]
     */
    private static array $totalsIndexedByEntityId;

    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \App\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     */
    public function __construct(PromiseAdapter $promiseAdapter, ProductElasticsearchProvider $productElasticsearchProvider)
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->productElasticsearchProvider = $productElasticsearchProvider;
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
     * @param \App\FrontendApi\Model\Product\BatchLoad\ProductBatchLoadByEntityData[] $productBatchLoadByEntitiesData
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByEntities(array $productBatchLoadByEntitiesData): Promise
    {
        $batchedByEntities = $this->productElasticsearchProvider->getBatchedByEntities($productBatchLoadByEntitiesData);
        self::$totalsIndexedByEntityId = $batchedByEntities[ProductElasticsearchRepository::TOTALS_KEY];

        return $this->promiseAdapter->all($batchedByEntities[ProductElasticsearchRepository::PRODUCTS_KEY]);
    }

    /**
     * @param int $entityId
     * @return int
     */
    public static function getTotalByEntityId(int $entityId): int
    {
        return self::$totalsIndexedByEntityId[$entityId] ?? 0;
    }
}

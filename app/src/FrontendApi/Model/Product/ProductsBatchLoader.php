<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product;

use App\Model\Product\ProductElasticsearchProvider;
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
        return $this->promiseAdapter->all($this->productElasticsearchProvider->getBatchedVisibleByProductIds($productsIds));
    }

    /**
     * @param \App\FrontendApi\Model\Product\BatchLoad\ProductBatchLoadByEntityData[] $productBatchLoadByEntitiesData
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByEntities(array $productBatchLoadByEntitiesData): Promise
    {
        return $this->promiseAdapter->all($this->productElasticsearchProvider->getBatchedByEntities($productBatchLoadByEntitiesData));
    }
}

<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product;

use App\Model\Product\ProductElasticsearchProvider;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductElasticsearchBatchProvider;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductElasticsearchBatchRepository;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductsBatchLoader as BaseProductsBatchLoader;

class ProductsBatchLoader extends BaseProductsBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductElasticsearchBatchProvider $productElasticsearchBatchProvider
     * @param \App\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     */
    public function __construct(
        PromiseAdapter $promiseAdapter,
        ProductElasticsearchBatchProvider $productElasticsearchBatchProvider,
        private readonly ProductElasticsearchProvider $productElasticsearchProvider,
    ) {
        parent::__construct($promiseAdapter, $productElasticsearchBatchProvider);
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductBatchLoadByEntityData[] $productBatchLoadByEntitiesData
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByEntities(array $productBatchLoadByEntitiesData): Promise
    {
        $batchedByEntities = $this->productElasticsearchProvider->getBatchedByEntities($productBatchLoadByEntitiesData);
        self::$totalsIndexedByBatchLoadDataId = $batchedByEntities[ProductElasticsearchBatchRepository::TOTALS_KEY];

        $result = [];

        foreach ($productBatchLoadByEntitiesData as $productBatchLoadByEntityData) {
            $result[] = $batchedByEntities[ProductElasticsearchBatchRepository::PRODUCTS_KEY][$productBatchLoadByEntityData->getId()];
        }

        return $this->promiseAdapter->all($result);
    }
}

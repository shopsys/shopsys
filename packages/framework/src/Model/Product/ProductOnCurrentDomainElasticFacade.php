<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountDataBatchLoadData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataRequest;

class ProductOnCurrentDomainElasticFacade
{
    public function __construct(
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
        protected readonly ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository,
        protected readonly FilterQueryFactory $filterQueryFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountDataBatchLoadData[] $batchLoadDataIndexedByKey
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData[] indexed by the same keys as the batch load data
     */
    public function getProductFilterCountDataByBatchLoadData(array $batchLoadDataIndexedByKey): array
    {
        $requestsIndexedByKey = [];

        foreach ($batchLoadDataIndexedByKey as $key => $batchLoadData) {
            $requestsIndexedByKey[$key] = $this->createProductFilterCountDataRequest($batchLoadData);
        }

        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataByRequests($requestsIndexedByKey);
    }

    protected function createProductFilterCountDataRequest(
        ProductFilterCountDataBatchLoadData $batchLoadData,
    ): ProductFilterCountDataRequest {
        $entity = $batchLoadData->getEntity();
        $productFilterData = $batchLoadData->getProductFilterData();

        return match (true) {
            $entity instanceof Category => new ProductFilterCountDataRequest(
                $productFilterData,
                $this->filterQueryFactory->createListableProductsByCategoryWithPriceAndStockFilter($entity, $productFilterData),
                true,
            ),
            $entity instanceof Brand => new ProductFilterCountDataRequest(
                $productFilterData,
                $this->filterQueryFactory->createListableProductsByBrandIdWithPriceAndStockFilter($entity->getId(), $productFilterData),
                true,
            ),
            $entity instanceof Flag => new ProductFilterCountDataRequest(
                $productFilterData,
                $this->filterQueryFactory->createListableProductsByFlagIdWithPriceAndStockFilter($entity->getId(), $productFilterData),
                true,
            ),
            $batchLoadData->getSearchText() !== '' => new ProductFilterCountDataRequest(
                $productFilterData,
                $this->filterQueryFactory->createListableProductsBySearchTextWithPriceAndStockFilter($batchLoadData->getSearchText(), $productFilterData),
                false,
            ),
            default => new ProductFilterCountDataRequest(
                $productFilterData,
                $this->filterQueryFactory->createListableProductsWithPriceAndStockFilter($productFilterData),
                false,
            ),
        };
    }

    /**
     * @return int[]
     */
    public function getCategoryIdsForFilterData(ProductFilterData $productFilterData): array
    {
        return $this->productElasticsearchRepository->getCategoryIdsForFilterData($productFilterData);
    }
}

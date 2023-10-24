<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\FrontendApi\Model\Product\BatchLoad\ProductBatchLoadByEntityData;
use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Search\FilterQuery;
use InvalidArgumentException;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider as BaseProductElasticsearchProvider;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductElasticsearchBatchRepository;

/**
 * @property \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
 * @property \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
 */
class ProductElasticsearchProvider extends BaseProductElasticsearchProvider
{
    /**
     * @param \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     * @param \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductElasticsearchBatchRepository $productElasticsearchBatchRepository
     */
    public function __construct(
        ProductElasticsearchRepository $productElasticsearchRepository,
        FilterQueryFactory $filterQueryFactory,
        private readonly ProductElasticsearchBatchRepository $productElasticsearchBatchRepository,
    ) {
        parent::__construct($productElasticsearchRepository, $filterQueryFactory);
    }

    /**
     * @param \App\FrontendApi\Model\Product\BatchLoad\ProductBatchLoadByEntityData[] $productBatchLoadByEntitiesData
     * @return array
     */
    public function getBatchedByEntities(array $productBatchLoadByEntitiesData): array
    {
        $filterQueries = [];

        foreach ($productBatchLoadByEntitiesData as $productBatchLoadByEntityData) {
            $filterQueries[$productBatchLoadByEntityData->getId()] = $this->getFilterQuery($productBatchLoadByEntityData);
        }

        return $this->productElasticsearchBatchRepository->getBatchedProductsAndTotalsByFilterQueries($filterQueries);
    }

    /**
     * @param \App\FrontendApi\Model\Product\BatchLoad\ProductBatchLoadByEntityData $productBatchLoadByEntityData
     * @return \App\Model\Product\Search\FilterQuery
     */
    private function getFilterQuery(ProductBatchLoadByEntityData $productBatchLoadByEntityData): FilterQuery
    {
        $entityClass = $productBatchLoadByEntityData->getEntityClass();

        switch ($entityClass) {
            case Category::class:
                $filterQuery = $this->getFilterQueryForCategory($productBatchLoadByEntityData);

                break;
            case Flag::class:
                $filterQuery = $this->getFilterQueryForFilterData($productBatchLoadByEntityData);

                break;
            case Brand::class:
                $filterQuery = $this->getFilterQueryForBrand($productBatchLoadByEntityData);

                break;
            default:
                throw new InvalidArgumentException(sprintf('Entity class "%s" is not supported for creating filter query', $entityClass));
        }

        $filterQuery = $filterQuery->setFrom($productBatchLoadByEntityData->getOffset());

        if ($productBatchLoadByEntityData->getSearch() !== '') {
            $filterQuery = $filterQuery->search($productBatchLoadByEntityData->getSearch());
        }

        return $filterQuery;
    }

    /**
     * @param \App\FrontendApi\Model\Product\BatchLoad\ProductBatchLoadByEntityData $productBatchLoadByEntityData
     * @return \App\Model\Product\Search\FilterQuery
     */
    private function getFilterQueryForCategory(ProductBatchLoadByEntityData $productBatchLoadByEntityData): FilterQuery
    {
        return $this->filterQueryFactory->createListableProductsByCategoryId(
            $productBatchLoadByEntityData->getProductFilterData(),
            $productBatchLoadByEntityData->getOrderingModeId(),
            1,
            $productBatchLoadByEntityData->getLimit(),
            $productBatchLoadByEntityData->getEntityId(),
        );
    }

    /**
     * @param \App\FrontendApi\Model\Product\BatchLoad\ProductBatchLoadByEntityData $productBatchLoadByEntityData
     * @return \App\Model\Product\Search\FilterQuery
     */
    private function getFilterQueryForFilterData(
        ProductBatchLoadByEntityData $productBatchLoadByEntityData,
    ): FilterQuery {
        return $this->filterQueryFactory->createWithProductFilterData(
            $productBatchLoadByEntityData->getProductFilterData(),
            $productBatchLoadByEntityData->getOrderingModeId(),
            1,
            $productBatchLoadByEntityData->getLimit(),
        );
    }

    /**
     * @param \App\FrontendApi\Model\Product\BatchLoad\ProductBatchLoadByEntityData $productBatchLoadByEntityData
     * @return \App\Model\Product\Search\FilterQuery
     */
    private function getFilterQueryForBrand(ProductBatchLoadByEntityData $productBatchLoadByEntityData): FilterQuery
    {
        return $this->filterQueryFactory->createListableProductsByBrandId(
            $productBatchLoadByEntityData->getProductFilterData(),
            $productBatchLoadByEntityData->getOrderingModeId(),
            1,
            $productBatchLoadByEntityData->getLimit(),
            $productBatchLoadByEntityData->getEntityId(),
        );
    }
}

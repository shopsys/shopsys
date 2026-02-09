<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\BatchLoad;

use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\CategoryAutomatedFilterFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;

class ProductElasticsearchBatchProvider
{
    public function __construct(
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly ProductElasticsearchBatchRepository $productElasticsearchBatchRepository,
        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly CategoryAutomatedFilterFacade $categoryAutomatedFilterFacade,
    ) {
    }

    /**
     * @param int[][] $productsIds
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
     */
    public function getBatchedSellableByProductIds(array $productsIds): array
    {
        $filterQueries = [];

        foreach ($productsIds as $productIds) {
            $filterQueries[] = $this->filterQueryFactory->createSellableProductsByProductIdsFilter($productIds, $this->productFrontendLimitProvider->getProductsFrontendLimit());
        }

        return $this->productElasticsearchBatchRepository->getBatchedProductsAndTotalsByFilterQueries($filterQueries);
    }

    /**
     * @param int[][] $productsIds
     */
    public function getBatchedProductGiftsAndIndexedByProductsIds(array $productsIds): array
    {
        $filterQueries = [];

        foreach ($productsIds as $mainProductId => $productIds) {
            $filterQueries[$mainProductId] = $this->filterQueryFactory->createSellableProductGiftsByProductIdsFilter(array_values($productIds), $this->productFrontendLimitProvider->getProductsFrontendLimit());
        }

        return $this->productElasticsearchBatchRepository->getBatchedProductsAndTotalsByFilterQueries($filterQueries);
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductSellableInCategoryBatchLoadData[] $sellableInCategoryBatchLoadData
     */
    public function getBatchedSellableInCategoryByIds(array $sellableInCategoryBatchLoadData): array
    {
        $filterQueries = [];

        foreach ($sellableInCategoryBatchLoadData as $batchLoadData) {
            $filterQueries[] = $this->getSellableByProductsIdsInCategoryFilterQuery($batchLoadData->productIds, $batchLoadData->category);
        }

        return $this->productElasticsearchBatchRepository->getBatchedProductsAndTotalsByFilterQueries($filterQueries);
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductBatchLoadByEntityData[] $productBatchLoadByEntitiesData
     */
    public function getBatchedByEntities(array $productBatchLoadByEntitiesData): array
    {
        $filterQueries = [];

        foreach ($productBatchLoadByEntitiesData as $productBatchLoadByEntityData) {
            $filterQueries[$productBatchLoadByEntityData->getId()] = $this->getFilterQuery($productBatchLoadByEntityData);
        }

        return $this->productElasticsearchBatchRepository->getBatchedProductsAndTotalsByFilterQueries($filterQueries);
    }

    protected function getFilterQuery(ProductBatchLoadByEntityData $productBatchLoadByEntityData): FilterQuery
    {
        $entity = $productBatchLoadByEntityData->getEntity();

        $filterQuery = match (true) {
            $entity instanceof Category => $this->getFilterQueryForCategory($productBatchLoadByEntityData),
            $entity instanceof Flag => $this->getFilterQueryForFilterData($productBatchLoadByEntityData),
            $entity instanceof Brand => $this->getFilterQueryForBrand($productBatchLoadByEntityData),
            default => throw new InvalidArgumentException(sprintf('Entity class "%s" is not supported for creating filter query', get_class($entity))),
        };

        $filterQuery = $filterQuery->setFrom($productBatchLoadByEntityData->getOffset());

        if ($productBatchLoadByEntityData->getSearch() !== '') {
            $filterQuery = $filterQuery->search($productBatchLoadByEntityData->getSearch());
        }

        return $filterQuery;
    }

    protected function getFilterQueryForCategory(
        ProductBatchLoadByEntityData $productBatchLoadByEntityData,
    ): FilterQuery {
        return $this->filterQueryFactory->createListableProductsByCategory(
            $productBatchLoadByEntityData->getProductFilterData(),
            $productBatchLoadByEntityData->getOrderingModeId(),
            1,
            $productBatchLoadByEntityData->getLimit(),
            $productBatchLoadByEntityData->getEntity($this->entityNameResolver->resolve(Category::class)),
        );
    }

    protected function getFilterQueryForFilterData(
        ProductBatchLoadByEntityData $productBatchLoadByEntityData,
    ): FilterQuery {
        return $this->filterQueryFactory->createWithProductFilterData(
            $productBatchLoadByEntityData->getProductFilterData(),
            $productBatchLoadByEntityData->getOrderingModeId(),
            1,
            $productBatchLoadByEntityData->getLimit(),
        );
    }

    protected function getFilterQueryForBrand(ProductBatchLoadByEntityData $productBatchLoadByEntityData): FilterQuery
    {
        return $this->filterQueryFactory->createListableProductsByBrand(
            $productBatchLoadByEntityData->getProductFilterData(),
            $productBatchLoadByEntityData->getOrderingModeId(),
            1,
            $productBatchLoadByEntityData->getLimit(),
            $productBatchLoadByEntityData->getEntity($this->entityNameResolver->resolve(Brand::class)),
        );
    }

    protected function getSellableByProductsIdsInCategoryFilterQuery(
        array $productIds,
        Category $category,
    ): FilterQuery {
        $filterQuery = $this->filterQueryFactory->createSellableProductsByProductIdsFilter(
            $productIds,
            $this->productFrontendLimitProvider->getProductsFrontendLimit(),
        );

        return $this->categoryAutomatedFilterFacade->applyFiltersByCategory($filterQuery, $category);
    }
}

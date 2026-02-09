<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\BatchLoad;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanFacade;

class ProductsBatchLoader
{
    /**
     * @var array<string, int>
     */
    protected static array $totalsIndexedByBatchLoadDataId;

    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly ProductElasticsearchBatchProvider $productElasticsearchBatchProvider,
        protected readonly Domain $domain,
        protected readonly GiftPlanFacade $giftPlanFacade,
    ) {
    }

    /**
     * @param int[][] $productsIds
     */
    public function loadVisibleByIds(array $productsIds): Promise
    {
        return $this->promiseAdapter->all($this->productElasticsearchBatchProvider->getBatchedVisibleByProductIds($productsIds)[ProductElasticsearchBatchRepository::PRODUCTS_KEY]);
    }

    /**
     * @param int[][] $productsIds
     */
    public function loadVisibleCountByIds(array $productsIds): Promise
    {
        return $this->promiseAdapter->all($this->productElasticsearchBatchProvider->getBatchedVisibleByProductIds($productsIds)[ProductElasticsearchBatchRepository::TOTALS_KEY]);
    }

    /**
     * @param int[][] $productsIds
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
     */
    public function loadSellableByIds(array $productsIds): Promise
    {
        return $this->promiseAdapter->all($this->productElasticsearchBatchProvider->getBatchedSellableByProductIds($productsIds)[ProductElasticsearchBatchRepository::PRODUCTS_KEY]);
    }

    public function loadSellableCountByIds(array $productsIds): Promise
    {
        return $this->promiseAdapter->all($this->productElasticsearchBatchProvider->getBatchedSellableByProductIds($productsIds)[ProductElasticsearchBatchRepository::TOTALS_KEY]);
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductSellableInCategoryBatchLoadData[] $sellableInCategoryBatchLoadData
     */
    public function loadSellableInCategoryByIds(array $sellableInCategoryBatchLoadData): Promise
    {
        return $this->promiseAdapter->all($this->productElasticsearchBatchProvider->getBatchedSellableInCategoryByIds($sellableInCategoryBatchLoadData)[ProductElasticsearchBatchRepository::PRODUCTS_KEY]);
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductSellableInCategoryBatchLoadData[] $sellableInCategoryBatchLoadData
     */
    public function loadSellableCountInCategoryByIds(array $sellableInCategoryBatchLoadData): Promise
    {
        return $this->promiseAdapter->all($this->productElasticsearchBatchProvider->getBatchedSellableInCategoryByIds($sellableInCategoryBatchLoadData)[ProductElasticsearchBatchRepository::TOTALS_KEY]);
    }

    public static function getTotalByBatchLoadDataId(string $batchLoadDataId): int
    {
        return self::$totalsIndexedByBatchLoadDataId[$batchLoadDataId] ?? 0;
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductBatchLoadByEntityData[] $productBatchLoadByEntitiesData
     */
    public function loadByEntities(array $productBatchLoadByEntitiesData): Promise
    {
        $batchedByEntities = $this->productElasticsearchBatchProvider->getBatchedByEntities($productBatchLoadByEntitiesData);
        self::$totalsIndexedByBatchLoadDataId = $batchedByEntities[ProductElasticsearchBatchRepository::TOTALS_KEY];

        $result = [];

        foreach ($productBatchLoadByEntitiesData as $productBatchLoadByEntityData) {
            $result[] = $batchedByEntities[ProductElasticsearchBatchRepository::PRODUCTS_KEY][$productBatchLoadByEntityData->getId()];
        }

        return $this->promiseAdapter->all($result);
    }

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

    /**
     * @param int[] $productIds
     */
    public function loadProductGiftsByMainProductIds(array $productIds): Promise
    {
        $domainId = $this->domain->getId();
        $giftProductIdsIndexedByMainProductId = $this->giftPlanFacade->findActiveGiftProductIdsByMainProductIds($productIds, $domainId);

        $isNonGiftsFound = array_filter($giftProductIdsIndexedByMainProductId) === [];

        if ($isNonGiftsFound) {
            return $this->promiseAdapter->all(
                array_map(fn () => [], $productIds),
            );
        }

        $batchedByEntities = $this->productElasticsearchBatchProvider->getBatchedProductGiftsAndIndexedByProductsIds($giftProductIdsIndexedByMainProductId);

        $result = [];

        foreach ($productIds as $mainProductIndex => $mainProductId) {
            $result[$mainProductIndex] = $batchedByEntities[ProductElasticsearchBatchRepository::PRODUCTS_KEY][$mainProductId] ?? [];
        }

        return $this->promiseAdapter->all($result);
    }
}

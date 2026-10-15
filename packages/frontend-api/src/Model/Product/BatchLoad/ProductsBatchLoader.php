<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\BatchLoad;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataBatchLoadDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanFacade;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Webmozart\Assert\Assert;

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
        protected readonly ProductFilterFacade $productFilterFacade,
        protected readonly ProductFilterDataFactory $productFilterDataFactory,
        protected readonly ProductFilterDataBatchLoadDataFactory $productFilterDataBatchLoadDataFactory,
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

    public static function getTotalByBatchLoadDataId(string $batchLoadDataId): int
    {
        return self::$totalsIndexedByBatchLoadDataId[$batchLoadDataId] ?? 0;
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductBatchLoadByEntityData[] $productBatchLoadByEntitiesData
     */
    public function loadByEntities(array $productBatchLoadByEntitiesData): Promise
    {
        $this->productFilterFacade->normalizeProductFilterDataByBatchLoadData(
            $this->createProductFilterDataBatchLoadData($productBatchLoadByEntitiesData),
        );
        $this->addListingFlagsToProductFilterData($productBatchLoadByEntitiesData);
        $this->addReadyCategorySeoMixesToProductFilterData($productBatchLoadByEntitiesData);

        $batchedByEntities = $this->productElasticsearchBatchProvider->getBatchedByEntities($productBatchLoadByEntitiesData);
        self::$totalsIndexedByBatchLoadDataId = $batchedByEntities[ProductElasticsearchBatchRepository::TOTALS_KEY];

        $result = [];

        foreach ($productBatchLoadByEntitiesData as $productBatchLoadByEntityData) {
            $result[] = $batchedByEntities[ProductElasticsearchBatchRepository::PRODUCTS_KEY][$productBatchLoadByEntityData->getId()];
        }

        return $this->promiseAdapter->all($result);
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductBatchLoadByEntityData[] $productBatchLoadByEntitiesData
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataBatchLoadData[]
     */
    protected function createProductFilterDataBatchLoadData(array $productBatchLoadByEntitiesData): array
    {
        $productFilterDataBatchLoadData = [];

        foreach ($productBatchLoadByEntitiesData as $key => $productBatchLoadByEntityData) {
            $entity = $productBatchLoadByEntityData->getEntity();
            Assert::isInstanceOfAny($entity, [Category::class, Brand::class, Flag::class]);

            $productFilterDataBatchLoadData[$key] = $this->productFilterDataBatchLoadDataFactory->create(
                $entity,
                '',
                $productBatchLoadByEntityData->getProductFilterData(),
            );
        }

        return $productFilterDataBatchLoadData;
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductBatchLoadByEntityData[] $productBatchLoadByEntitiesData
     */
    protected function addListingFlagsToProductFilterData(array $productBatchLoadByEntitiesData): void
    {
        foreach ($productBatchLoadByEntitiesData as $productBatchLoadByEntityData) {
            $entity = $productBatchLoadByEntityData->getEntity();

            if ($entity instanceof Flag) {
                $this->productFilterFacade->addListingFlagToProductFilterData($entity, $productBatchLoadByEntityData->getProductFilterData());
            }
        }
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductBatchLoadByEntityData[] $productBatchLoadByEntitiesData
     */
    protected function addReadyCategorySeoMixesToProductFilterData(array $productBatchLoadByEntitiesData): void
    {
        foreach ($productBatchLoadByEntitiesData as $productBatchLoadByEntityData) {
            $readyCategorySeoMix = $productBatchLoadByEntityData->getReadyCategorySeoMix();

            if ($readyCategorySeoMix !== null) {
                $this->productFilterDataFactory->updateProductFilterDataFromReadyCategorySeoMix($readyCategorySeoMix, $productBatchLoadByEntityData->getProductFilterData());
            }
        }
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

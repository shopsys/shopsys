<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterBatchLoadData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterBatchLoadDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataBatchLoadData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrontendApiBundle\Model\Resolver\Customer\Error\CustomerUserAccessDeniedUserError;

class ProductFilterFacade
{
    protected const string PRODUCT_FILTER_CACHE_NAMESPACE = 'productFilterConfig';

    public function __construct(
        protected readonly Domain $domain,
        protected readonly ProductFilterDataMapper $productFilterDataMapper,
        protected readonly ProductFilterNormalizer $productFilterNormalizer,
        protected readonly ProductFilterConfigFactory $productFilterConfigFactory,
        protected readonly ProductFilterDataFactory $productFilterDataFactory,
        protected readonly CustomerUserRoleResolver $customerUserRoleResolver,
        protected readonly InMemoryCache $inMemoryCache,
        protected readonly ProductFilterBatchLoadDataFactory $productFilterBatchLoadDataFactory,
    ) {
    }

    public function getProductFilterConfigForAll(): ProductFilterConfig
    {
        return array_first($this->getProductFilterConfigsByBatchLoadData([$this->productFilterBatchLoadDataFactory->create(null, '')]));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterBatchLoadData[] $batchLoadDataIndexedByKey
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig[] indexed by the same keys as the batch load data
     */
    public function getProductFilterConfigsByBatchLoadData(array $batchLoadDataIndexedByKey): array
    {
        $cacheKeyPartsIndexedByKey = [];
        $cacheKeyPartsIndexedByCacheKey = [];
        $batchLoadDataToCreateIndexedByCacheKey = [];

        foreach ($batchLoadDataIndexedByKey as $key => $batchLoadData) {
            $cacheKeyParts = $this->getProductFilterConfigCacheKeyParts($batchLoadData);
            $cacheKeyPartsIndexedByKey[$key] = $cacheKeyParts;

            if ($this->inMemoryCache->hasItem(static::PRODUCT_FILTER_CACHE_NAMESPACE, ...$cacheKeyParts)) {
                continue;
            }

            $cacheKey = $this->inMemoryCache->getKey(...$cacheKeyParts);
            $cacheKeyPartsIndexedByCacheKey[$cacheKey] = $cacheKeyParts;
            $batchLoadDataToCreateIndexedByCacheKey[$cacheKey] = $batchLoadData;
        }

        if ($batchLoadDataToCreateIndexedByCacheKey !== []) {
            $createdProductFilterConfigsIndexedByCacheKey = $this->productFilterConfigFactory->createByBatchLoadData(
                $batchLoadDataToCreateIndexedByCacheKey,
                $this->domain->getLocale(),
            );

            foreach ($createdProductFilterConfigsIndexedByCacheKey as $cacheKey => $productFilterConfig) {
                $this->inMemoryCache->save(
                    static::PRODUCT_FILTER_CACHE_NAMESPACE,
                    $productFilterConfig,
                    ...$cacheKeyPartsIndexedByCacheKey[$cacheKey],
                );
            }
        }

        $productFilterConfigsIndexedByKey = [];

        foreach ($cacheKeyPartsIndexedByKey as $key => $cacheKeyParts) {
            $productFilterConfigsIndexedByKey[$key] = $this->inMemoryCache->getItem(static::PRODUCT_FILTER_CACHE_NAMESPACE, ...$cacheKeyParts);
        }

        return $productFilterConfigsIndexedByKey;
    }

    /**
     * @return array<int, string|int>
     */
    protected function getProductFilterConfigCacheKeyParts(ProductFilterBatchLoadData $batchLoadData): array
    {
        $entity = $batchLoadData->getEntity();

        return match (true) {
            $entity instanceof Category => ['category', $entity->getId()],
            $entity instanceof Brand => ['brand', $entity->getId()],
            $entity instanceof Flag => ['flag', $entity->getId()],
            $batchLoadData->getSearchText() !== '' => ['search', $batchLoadData->getSearchText()],
            default => ['all'],
        };
    }

    public function getProductFilterData(Argument $argument): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterData = $this->productFilterDataMapper->mapFrontendApiFilterToProductFilterData($argument['filter']);

        if (!$this->customerUserRoleResolver->canCurrentCustomerUserSeePrices()) {
            if ($productFilterData->maximalPrice !== null || $productFilterData->minimalPrice !== null) {
                throw new CustomerUserAccessDeniedUserError('Filtering by price is not allowed for current user.');
            }
        }

        return $productFilterData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataBatchLoadData[] $batchLoadDataIndexedByKey
     */
    public function normalizeProductFilterDataByBatchLoadData(array $batchLoadDataIndexedByKey): void
    {
        $batchLoadDataToNormalizeIndexedByKey = array_filter(
            $batchLoadDataIndexedByKey,
            fn (ProductFilterDataBatchLoadData $batchLoadData): bool => $this->hasFiltersToNormalize($batchLoadData->getProductFilterData()),
        );

        if ($batchLoadDataToNormalizeIndexedByKey === []) {
            return;
        }

        $productFilterConfigsIndexedByKey = $this->getProductFilterConfigsByBatchLoadData($batchLoadDataIndexedByKey);

        foreach ($batchLoadDataToNormalizeIndexedByKey as $key => $batchLoadData) {
            $this->productFilterNormalizer->removeExcessiveFilters(
                $batchLoadData->getProductFilterData(),
                $productFilterConfigsIndexedByKey[$key],
            );
        }
    }

    protected function hasFiltersToNormalize(ProductFilterData $productFilterData): bool
    {
        return $productFilterData->flags !== [] || $productFilterData->brands !== [] || $productFilterData->parameters !== [];
    }

    public function addListingFlagToProductFilterData(Flag $flag, ProductFilterData $productFilterData): void
    {
        if (!in_array($flag, $productFilterData->flags, true)) {
            $productFilterData->flags[] = $flag;
        }
    }

    public function getValidatedProductFilterDataForAll(Argument $argument): ProductFilterData
    {
        $productFilterData = $this->getProductFilterData($argument);

        if ($this->hasFiltersToNormalize($productFilterData)) {
            $this->productFilterNormalizer->removeExcessiveFilters($productFilterData, $this->getProductFilterConfigForAll());
        }

        return $productFilterData;
    }
}

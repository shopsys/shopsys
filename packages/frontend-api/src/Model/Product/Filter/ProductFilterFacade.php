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
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
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
    ) {
    }

    public function getProductFilterConfigForAll(): ProductFilterConfig
    {
        return $this->getProductFilterConfigByBatchLoadData(new ProductFilterBatchLoadData(null, ''));
    }

    public function getProductFilterConfigForBrand(Brand $brand): ProductFilterConfig
    {
        return $this->getProductFilterConfigByBatchLoadData(new ProductFilterBatchLoadData($brand, ''));
    }

    public function getProductFilterConfigForCategory(Category $category): ProductFilterConfig
    {
        return $this->getProductFilterConfigByBatchLoadData(new ProductFilterBatchLoadData($category, ''));
    }

    public function getProductFilterConfigForFlag(Flag $flag): ProductFilterConfig
    {
        return $this->getProductFilterConfigByBatchLoadData(new ProductFilterBatchLoadData($flag, ''));
    }

    protected function getProductFilterConfigByBatchLoadData(
        ProductFilterBatchLoadData $batchLoadData,
    ): ProductFilterConfig {
        return array_first($this->getProductFilterConfigsByBatchLoadData([$batchLoadData]));
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

            $cacheKey = implode('~', $cacheKeyParts);
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

    protected function getValidatedProductFilterData(
        Argument $argument,
        ProductFilterConfig $productFilterConfig,
    ): ProductFilterData {
        $productFilterData = $this->productFilterDataMapper->mapFrontendApiFilterToProductFilterData(
            $argument['filter'],
        );

        $this->productFilterNormalizer->removeExcessiveFilters($productFilterData, $productFilterConfig);

        if (!$this->customerUserRoleResolver->canCurrentCustomerUserSeePrices()) {
            if ($productFilterData->maximalPrice !== null || $productFilterData->minimalPrice !== null) {
                throw new CustomerUserAccessDeniedUserError('Filtering by price is not allowed for current user.');
            }
        }

        return $productFilterData;
    }

    public function getValidatedProductFilterDataForAll(Argument $argument): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterConfig = $this->getProductFilterConfigForAll();

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }

    public function getValidatedProductFilterDataForCategory(Argument $argument, Category $category): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterConfig = $this->getProductFilterConfigForCategory($category);

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }

    public function getValidatedProductFilterDataForBrand(Argument $argument, Brand $brand): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterConfig = $this->getProductFilterConfigForBrand($brand);

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }

    public function getValidatedProductFilterDataForFlag(Argument $argument, Flag $flag): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterConfig = $this->getProductFilterConfigForFlag($flag);

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }
}

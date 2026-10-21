<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Closure;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigIdsData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterElasticFacade;
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
        protected readonly ProductFilterElasticFacade $productFilterElasticFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly DataLoaderInterface $elasticsearchSearchBatchLoader,
    ) {
    }

    public function getProductFilterConfigForAll(): ProductFilterConfig
    {
        return $this->getProductFilterConfig(
            $this->createAggregationQueryForAll(...),
            $this->createProductFilterConfigForAll(...),
            'all',
        );
    }

    public function getProductFilterConfigForBrand(Brand $brand): ProductFilterConfig
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::PRODUCT_FILTER_CACHE_NAMESPACE,
            function () use ($brand) {
                return $this->productFilterConfigFactory->createForBrand(
                    $this->domain->getLocale(),
                    $brand,
                );
            },
            'brand',
            $brand->getId(),
        );
    }

    public function getProductFilterConfigForCategory(Category $category): ProductFilterConfig
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::PRODUCT_FILTER_CACHE_NAMESPACE,
            function () use ($category) {
                return $this->productFilterConfigFactory->createForCategory(
                    $this->domain->getLocale(),
                    $category,
                );
            },
            'category',
            $category->getId(),
        );
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

    public function getProductFilterConfigForFlag(Flag $flag): ProductFilterConfig
    {
        $locale = $this->domain->getLocale();

        return $this->getProductFilterConfig(
            fn (): array => $this->createAggregationQueryForFlag($flag),
            fn (ProductFilterConfigIdsData $productFilterConfigIdsData): ProductFilterConfig => $this->createProductFilterConfigForFlag(
                $productFilterConfigIdsData,
                $locale,
            ),
            'flag',
            $locale,
            $flag->getId(),
        );
    }

    public function getProductFilterConfigPromiseForAll(): Promise
    {
        return $this->getProductFilterConfigPromise(
            $this->createAggregationQueryForAll(...),
            $this->createProductFilterConfigForAll(...),
            'all',
        );
    }

    public function getProductFilterConfigPromiseForSearch(string $searchText): Promise
    {
        return $this->getProductFilterConfigPromise(
            fn (): array => $this->productFilterElasticFacade->getAggregationQueryForSearch(
                $searchText,
                $this->currentCustomerUser->getPricingGroup(),
            ),
            fn (ProductFilterConfigIdsData $productFilterConfigIdsData): ProductFilterConfig => $this->productFilterConfigFactory->createWithFlagAndBrandChoices(
                $productFilterConfigIdsData,
                $this->domain->getLocale(),
            ),
            'search',
            $searchText,
        );
    }

    public function getProductFilterConfigPromiseForBrand(Brand $brand): Promise
    {
        return $this->getProductFilterConfigPromise(
            fn (): array => $this->productFilterElasticFacade->getAggregationQueryInBrand(
                $brand->getId(),
                $this->currentCustomerUser->getPricingGroup(),
            ),
            fn (ProductFilterConfigIdsData $productFilterConfigIdsData): ProductFilterConfig => $this->productFilterConfigFactory->createWithFlagChoices(
                $productFilterConfigIdsData,
                $this->domain->getLocale(),
            ),
            'brand',
            $brand->getId(),
        );
    }

    public function getProductFilterConfigPromiseForCategory(Category $category): Promise
    {
        return $this->getProductFilterConfigPromise(
            fn (): array => $this->productFilterElasticFacade->getAggregationQueryInCategory(
                $category,
                $this->currentCustomerUser->getPricingGroup(),
            ),
            fn (ProductFilterConfigIdsData $productFilterConfigIdsData): ProductFilterConfig => $this->productFilterConfigFactory->createWithParameterFlagAndBrandChoices(
                $productFilterConfigIdsData,
                $this->domain->getLocale(),
                $category,
            ),
            'category',
            $category->getId(),
        );
    }

    public function getProductFilterConfigPromiseForFlag(Flag $flag): Promise
    {
        $locale = $this->domain->getLocale();

        return $this->getProductFilterConfigPromise(
            fn (): array => $this->createAggregationQueryForFlag($flag),
            fn (ProductFilterConfigIdsData $productFilterConfigIdsData): ProductFilterConfig => $this->createProductFilterConfigForFlag(
                $productFilterConfigIdsData,
                $locale,
            ),
            'flag',
            $locale,
            $flag->getId(),
        );
    }

    /**
     * @return array{index: string, body: array<string, mixed>}
     */
    protected function createAggregationQueryForAll(): array
    {
        return $this->productFilterElasticFacade->getAggregationQueryForAll(
            $this->currentCustomerUser->getPricingGroup(),
        );
    }

    protected function createProductFilterConfigForAll(
        ProductFilterConfigIdsData $productFilterConfigIdsData,
    ): ProductFilterConfig {
        return $this->productFilterConfigFactory->createWithFlagAndBrandChoices(
            $productFilterConfigIdsData,
            $this->domain->getLocale(),
        );
    }

    /**
     * @return array{index: string, body: array<string, mixed>}
     */
    protected function createAggregationQueryForFlag(Flag $flag): array
    {
        return $this->productFilterElasticFacade->getAggregationQueryInFlag(
            $flag->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );
    }

    protected function createProductFilterConfigForFlag(
        ProductFilterConfigIdsData $productFilterConfigIdsData,
        string $locale,
    ): ProductFilterConfig {
        return $this->productFilterConfigFactory->createWithFlagAndBrandChoices(
            $productFilterConfigIdsData,
            $locale,
        );
    }

    protected function getProductFilterConfig(
        Closure $createAggregationQuery,
        Closure $createProductFilterConfig,
        mixed ...$cacheKeyParts,
    ): ProductFilterConfig {
        return $this->inMemoryCache->getOrSaveValue(
            static::PRODUCT_FILTER_CACHE_NAMESPACE,
            fn (): ProductFilterConfig => $createProductFilterConfig(
                $this->productFilterElasticFacade->getProductFilterConfigIdsData($createAggregationQuery()),
            ),
            ...$cacheKeyParts,
        );
    }

    protected function getProductFilterConfigPromise(
        Closure $createAggregationQuery,
        Closure $createProductFilterConfig,
        mixed ...$cacheKeyParts,
    ): Promise {
        if ($this->inMemoryCache->hasItem(static::PRODUCT_FILTER_CACHE_NAMESPACE, ...$cacheKeyParts)) {
            return $this->promiseAdapter->createFulfilled(
                $this->inMemoryCache->getItem(static::PRODUCT_FILTER_CACHE_NAMESPACE, ...$cacheKeyParts),
            );
        }

        return $this->elasticsearchSearchBatchLoader->load($createAggregationQuery())->then(
            fn (array $aggregationResponse): ProductFilterConfig => $this->inMemoryCache->getOrSaveValue(
                static::PRODUCT_FILTER_CACHE_NAMESPACE,
                fn (): ProductFilterConfig => $createProductFilterConfig(
                    $this->productFilterElasticFacade->createProductFilterConfigIdsData($aggregationResponse),
                ),
                ...$cacheKeyParts,
            ),
        );
    }
}

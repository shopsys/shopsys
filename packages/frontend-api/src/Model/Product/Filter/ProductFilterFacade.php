<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
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
        return $this->inMemoryCache->getOrSaveValue(
            static::PRODUCT_FILTER_CACHE_NAMESPACE,
            function () {
                return $this->productFilterConfigFactory->createForAll(
                    $this->domain->getId(),
                    $this->domain->getLocale(),
                );
            },
            'all',
        );
    }

    public function getProductFilterConfigForBrand(Brand $brand): ProductFilterConfig
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::PRODUCT_FILTER_CACHE_NAMESPACE,
            function () use ($brand) {
                return $this->productFilterConfigFactory->createForBrand(
                    $this->domain->getId(),
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

    public function getProductFilterConfigForSearch(string $searchText): ProductFilterConfig
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::PRODUCT_FILTER_CACHE_NAMESPACE,
            function () use ($searchText) {
                return $this->productFilterConfigFactory->createForSearch(
                    $this->domain->getId(),
                    $this->domain->getLocale(),
                    $searchText,
                );
            },
            'search',
            $searchText,
        );
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

        return $this->inMemoryCache->getOrSaveValue(
            static::PRODUCT_FILTER_CACHE_NAMESPACE,
            function () use ($flag, $locale) {
                return $this->productFilterConfigFactory->createForFlag(
                    $flag,
                    $locale,
                );
            },
            'flag',
            $locale,
            $flag->getId(),
        );
    }
}

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

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper $productFilterDataMapper
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterNormalizer $productFilterNormalizer
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory $productFilterConfigFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver $customerUserRoleResolver
     * @param \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache
     */
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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
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

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData
     */
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

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData
     */
    public function getValidatedProductFilterDataForAll(Argument $argument): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterConfig = $this->getProductFilterConfigForAll();

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData
     */
    public function getValidatedProductFilterDataForCategory(Argument $argument, Category $category): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterConfig = $this->getProductFilterConfigForCategory($category);

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData
     */
    public function getValidatedProductFilterDataForBrand(Argument $argument, Brand $brand): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterConfig = $this->getProductFilterConfigForBrand($brand);

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }

    /**
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
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

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData
     */
    public function getValidatedProductFilterDataForFlag(Argument $argument, Flag $flag): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterConfig = $this->getProductFilterConfigForFlag($flag);

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
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

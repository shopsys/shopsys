<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use App\Model\Product\Filter\Elasticsearch\ProductFilterConfigFactory;
use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Filter\ProductFilterDataFactory;
use App\Model\Product\Flag\Flag;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade as BaseProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterNormalizer;

/**
 * @property \App\FrontendApi\Model\Product\Filter\ProductFilterDataMapper $productFilterDataMapper
 * @property \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigFactory $productFilterConfigFactory
 * @method \App\Model\Product\Filter\ProductFilterData getValidatedProductFilterData(\Overblog\GraphQLBundle\Definition\Argument $argument, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig)
 */
class ProductFilterFacade extends BaseProductFilterFacade
{
    /**
     * @var \App\Model\Product\Filter\ProductFilterDataFactory
     */
    private ProductFilterDataFactory $productFilterDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Product\Filter\ProductFilterDataMapper $productFilterDataMapper
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterNormalizer $productFilterNormalizer
     * @param \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigFactory $productFilterConfigFactory
     * @param \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     */
    public function __construct(
        Domain $domain,
        ProductFilterDataMapper $productFilterDataMapper,
        ProductFilterNormalizer $productFilterNormalizer,
        ProductFilterConfigFactory $productFilterConfigFactory,
        ProductFilterDataFactory $productFilterDataFactory
    ) {
        parent::__construct(
            $domain,
            $productFilterDataMapper,
            $productFilterNormalizer,
            $productFilterConfigFactory
        );

        $this->productFilterDataFactory = $productFilterDataFactory;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Flag\Flag $flag
     * @return \App\Model\Product\Filter\ProductFilterData
     */
    public function getValidatedProductFilterDataForFlag(Argument $argument, Flag $flag): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterConfig = $this->getProductFilterConfigForFlag($flag, $argument['search'] ?? '');

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function getProductFilterConfigForFlag(Flag $flag, string $searchText = ''): ProductFilterConfig
    {
        $locale = $this->domain->getLocale();
        $cacheKey = sprintf('flag_%s_%s_search_%s', $locale, $flag->getId(), $searchText);

        if (!array_key_exists($cacheKey, $this->productFilterConfigCache)) {
            $this->productFilterConfigCache[$cacheKey] = $this->productFilterConfigFactory->createForFlag(
                $flag,
                $locale,
                $searchText
            );
        }

        return $this->productFilterConfigCache[$cacheKey];
    }

    /**
     * Method is extended because of https://github.com/shopsys/shopsys/pull/2380
     *
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Product\Filter\ProductFilterData
     */
    public function getValidatedProductFilterDataForAll(Argument $argument): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        /** @var \App\Model\Product\Filter\ProductFilterData $productFilterData */
        $productFilterData = parent::getValidatedProductFilterDataForAll($argument);

        return $productFilterData;
    }

    /**
     * Method is extended because of https://github.com/shopsys/shopsys/pull/2380
     *
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Product\Filter\ProductFilterData
     */
    public function getValidatedProductFilterDataForCategory(Argument $argument, Category $category): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterConfig = $this->getProductFilterConfigForCategory($category, $argument['search'] ?? '');

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }

    /**
     * Method is extended because of https://github.com/shopsys/shopsys/pull/2380
     *
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Brand\Brand $brand
     * @return \App\Model\Product\Filter\ProductFilterData
     */
    public function getValidatedProductFilterDataForBrand(Argument $argument, Brand $brand): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterConfig = $this->getProductFilterConfigForBrand($brand, $argument['search'] ?? '');

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }

    /**
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function getProductFilterConfigForSearch(string $searchText): ProductFilterConfig
    {
        $cacheKey = 'search_' . $searchText;

        if (!array_key_exists($cacheKey, $this->productFilterConfigCache)) {
            $this->productFilterConfigCache[$cacheKey] = $this->productFilterConfigFactory->createForSearch(
                $this->domain->getId(),
                $this->domain->getLocale(),
                $searchText
            );
        }

        return $this->productFilterConfigCache[$cacheKey];
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function getProductFilterConfigForCategory(Category $category, string $searchText = ''): ProductFilterConfig
    {
        $cacheKey = 'category_' . $category->getId() . '_search_' . $searchText;

        if (!array_key_exists($cacheKey, $this->productFilterConfigCache)) {
            $this->productFilterConfigCache[$cacheKey] = $this->productFilterConfigFactory->createForCategory(
                $this->domain->getId(),
                $this->domain->getLocale(),
                $category,
                $searchText
            );
        }

        return $this->productFilterConfigCache[$cacheKey];
    }

    /**
     * @param \App\Model\Product\Brand\Brand $brand
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function getProductFilterConfigForBrand(Brand $brand, string $searchText = ''): ProductFilterConfig
    {
        $cacheKey = 'brand_' . $brand->getId() . '_search_' . $searchText;

        if (!array_key_exists($cacheKey, $this->productFilterConfigCache)) {
            $this->productFilterConfigCache[$cacheKey] = $this->productFilterConfigFactory->createForBrand(
                $this->domain->getId(),
                $this->domain->getLocale(),
                $brand,
                $searchText
            );
        }

        return $this->productFilterConfigCache[$cacheKey];
    }
}

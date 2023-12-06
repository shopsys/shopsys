<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Flag\Flag;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade as BaseProductFilterFacade;

/**
 * @property \App\FrontendApi\Model\Product\Filter\ProductFilterDataMapper $productFilterDataMapper
 * @property \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigFactory $productFilterConfigFactory
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData getValidatedProductFilterData(\Overblog\GraphQLBundle\Definition\Argument $argument, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig getProductFilterConfigForCategory(\App\Model\Category\Category $category, string $searchText = "")
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData getValidatedProductFilterDataForAll(\Overblog\GraphQLBundle\Definition\Argument $argument)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData getValidatedProductFilterDataForCategory(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Category\Category $category)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData getValidatedProductFilterDataForBrand(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Product\Brand\Brand $brand)
 * @property \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
 * @method __construct(\Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\FrontendApi\Model\Product\Filter\ProductFilterDataMapper $productFilterDataMapper, \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterNormalizer $productFilterNormalizer, \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigFactory $productFilterConfigFactory, \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory)
 */
class ProductFilterFacade extends BaseProductFilterFacade
{
    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Flag\Flag $flag
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData
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
                $searchText,
            );
        }

        return $this->productFilterConfigCache[$cacheKey];
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
                $searchText,
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
                $searchText,
            );
        }

        return $this->productFilterConfigCache[$cacheKey];
    }
}

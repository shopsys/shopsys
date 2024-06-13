<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use App\Model\Product\Flag\Flag;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade as BaseProductFilterFacade;

/**
 * @property \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigFactory $productFilterConfigFactory
 * @property \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
 * @method __construct(\Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper $productFilterDataMapper, \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterNormalizer $productFilterNormalizer, \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigFactory $productFilterConfigFactory, \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig getProductFilterConfigForBrand(\App\Model\Product\Brand\Brand $brand)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig getProductFilterConfigForCategory(\App\Model\Category\Category $category)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData getValidatedProductFilterDataForCategory(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Category\Category $category)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData getValidatedProductFilterDataForBrand(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Product\Brand\Brand $brand)
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

        $productFilterConfig = $this->getProductFilterConfigForFlag($flag);

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function getProductFilterConfigForFlag(Flag $flag): ProductFilterConfig
    {
        $locale = $this->domain->getLocale();
        $cacheKey = sprintf('flag_%s_%s', $locale, $flag->getId());

        if (!array_key_exists($cacheKey, $this->productFilterConfigCache)) {
            $this->productFilterConfigCache[$cacheKey] = $this->productFilterConfigFactory->createForFlag(
                $flag,
                $locale,
            );
        }

        return $this->productFilterConfigCache[$cacheKey];
    }
}

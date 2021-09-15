<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use App\Model\Product\Filter\Elasticsearch\ProductFilterConfigFactory;
use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Filter\ProductFilterDataFactory;
use App\Model\Product\Flag\Flag;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade as BaseProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterNormalizer;

/**
 * @property \App\FrontendApi\Model\Product\Filter\ProductFilterDataMapper $productFilterDataMapper
 * @property \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigFactory $productFilterConfigFactory
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig getProductFilterConfigForBrand(\App\Model\Product\Brand\Brand $brand)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig getProductFilterConfigForCategory(\App\Model\Category\Category $category)
 * @method \App\Model\Product\Filter\ProductFilterData getValidatedProductFilterData(\Overblog\GraphQLBundle\Definition\Argument $argument, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig)
 * @method \App\Model\Product\Filter\ProductFilterData getValidatedProductFilterDataForAll(\Overblog\GraphQLBundle\Definition\Argument $argument)
 * @method \App\Model\Product\Filter\ProductFilterData getValidatedProductFilterDataForCategory(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Category\Category $category)
 * @method \App\Model\Product\Filter\ProductFilterData getValidatedProductFilterDataForBrand(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Product\Brand\Brand $brand)
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

        $productFilterConfig = $this->getProductFilterConfigForFlag($flag);

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function getProductFilterConfigForFlag(Flag $flag): ProductFilterConfig
    {
        $cacheKey = 'flag_' . $flag->getId();

        if (!array_key_exists($cacheKey, $this->productFilterConfigCache)) {
            $this->productFilterConfigCache[$cacheKey] = $this->productFilterConfigFactory->createForFlag(
                $flag
            );
        }

        return $this->productFilterConfigCache[$cacheKey];
    }
}

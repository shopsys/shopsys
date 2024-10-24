<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade as BaseProductFilterFacade;

/**
 * @property \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigFactory $productFilterConfigFactory
 * @property \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
 * @method __construct(\Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper $productFilterDataMapper, \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterNormalizer $productFilterNormalizer, \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigFactory $productFilterConfigFactory, \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory, \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver $customerUserRoleResolver, \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig getProductFilterConfigForBrand(\App\Model\Product\Brand\Brand $brand)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig getProductFilterConfigForCategory(\App\Model\Category\Category $category)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData getValidatedProductFilterDataForCategory(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Category\Category $category)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData getValidatedProductFilterDataForBrand(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Product\Brand\Brand $brand)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData getValidatedProductFilterDataForFlag(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Product\Flag\Flag $flag)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig getProductFilterConfigForFlag(\App\Model\Product\Flag\Flag $flag)
 */
class ProductFilterFacade extends BaseProductFilterFacade
{
}

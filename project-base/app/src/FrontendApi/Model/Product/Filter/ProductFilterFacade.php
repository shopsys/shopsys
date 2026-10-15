<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade as BaseProductFilterFacade;

/**
 * @property \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
 * @method __construct(\Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper $productFilterDataMapper, \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterNormalizer $productFilterNormalizer, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory $productFilterConfigFactory, \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory, \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver $customerUserRoleResolver, \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterBatchLoadDataFactory $productFilterBatchLoadDataFactory)
 * @method void addListingFlagToProductFilterData(\App\Model\Product\Flag\Flag $flag, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 */
class ProductFilterFacade extends BaseProductFilterFacade
{
}

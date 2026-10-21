<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade as BaseProductFilterFacade;

/**
 * @property \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
 * @method __construct(\Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper $productFilterDataMapper, \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterNormalizer $productFilterNormalizer, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory $productFilterConfigFactory, \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory, \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver $customerUserRoleResolver, \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterElasticFacade $productFilterElasticFacade, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter, \Overblog\DataLoader\DataLoaderInterface $elasticsearchSearchBatchLoader)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData getValidatedProductFilterDataForFlag(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Product\Flag\Flag $flag)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig getProductFilterConfigForFlag(\App\Model\Product\Flag\Flag $flag)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @method \GraphQL\Executor\Promise\Promise getProductFilterConfigPromiseForBrand(\App\Model\Product\Brand\Brand $brand)
 * @method \GraphQL\Executor\Promise\Promise getProductFilterConfigPromiseForCategory(\App\Model\Category\Category $category)
 * @method \GraphQL\Executor\Promise\Promise getProductFilterConfigPromiseForFlag(\App\Model\Product\Flag\Flag $flag)
 * @method \GraphQL\Executor\Promise\Promise getValidatedProductFilterDataPromiseForCategory(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Category\Category $category)
 * @method \GraphQL\Executor\Promise\Promise getValidatedProductFilterDataPromiseForBrand(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Product\Brand\Brand $brand)
 * @method \GraphQL\Executor\Promise\Promise getValidatedProductFilterDataPromiseForFlag(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Product\Flag\Flag $flag)
 */
class ProductFilterFacade extends BaseProductFilterFacade
{
}

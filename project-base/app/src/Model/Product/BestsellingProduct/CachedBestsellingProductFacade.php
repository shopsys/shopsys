<?php

declare(strict_types=1);

namespace App\Model\Product\BestsellingProduct;

use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade as BaseCachedBestsellingProductFacade;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @method __construct(\Symfony\Contracts\Cache\CacheInterface $cache, \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductFacade $bestsellingProductFacade, \App\Model\Product\ProductRepository $productRepository, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository)
 * @method invalidateCacheByDomainIdAndCategory(int $domainId, \App\Model\Category\Category $category)
 * @method string getCacheId(int $domainId, \App\Model\Category\Category $category, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method int[] getAllOfferedBestsellingProductIds(int $domainId, \App\Model\Category\Category $category, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 */
class CachedBestsellingProductFacade extends BaseCachedBestsellingProductFacade
{
}

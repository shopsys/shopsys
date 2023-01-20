<?php

declare(strict_types=1);

namespace App\Model\Product\BestsellingProduct;

use App\Component\Deprecation\DeprecatedMethodException;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade as BaseCachedBestsellingProductFacade;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @method __construct(\Symfony\Contracts\Cache\CacheInterface $cache, \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductFacade $bestsellingProductFacade, \App\Model\Product\ProductRepository $productRepository, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository)
 * @method invalidateCacheByDomainIdAndCategory(int $domainId, \App\Model\Category\Category $category)
 * @method \App\Model\Product\Product[] getSortedProducts(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int[] $sortedProductIds)
 * @method string getCacheId(int $domainId, \App\Model\Category\Category $category, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 */
class CachedBestsellingProductFacade extends BaseCachedBestsellingProductFacade
{
    /**
     * @deprecated Method is deprecated. Use "getAllOfferedBestsellingProductIds()" instead.
     * @param int $domainId
     * @param \App\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return array|\App\Model\Product\Product[]
     */
    public function getAllOfferedBestsellingProducts($domainId, Category $category, PricingGroup $pricingGroup): array
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param int $domainId
     * @param \App\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return int[]
     */
    public function getAllOfferedBestsellingProductIds(int $domainId, Category $category, PricingGroup $pricingGroup): array
    {
        $cacheId = $this->getCacheId($domainId, $category, $pricingGroup);

        return $this->cache->get(
            $cacheId,
            function () use ($domainId, $category, $pricingGroup) {

                /** @var \App\Model\Product\Product[] $bestsellingProducts */
                $bestsellingProducts = $this->bestsellingProductFacade->getAllOfferedBestsellingProducts(
                    $domainId,
                    $category,
                    $pricingGroup
                );

                return array_map(
                    function (Product $product): int {
                        return $product->getId();
                    },
                    $bestsellingProducts
                );
            }
        );
    }
}

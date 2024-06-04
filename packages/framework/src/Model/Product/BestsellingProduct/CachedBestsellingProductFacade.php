<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\BestsellingProduct;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Symfony\Contracts\Cache\CacheInterface;

class CachedBestsellingProductFacade
{
    /**
     * @param \Symfony\Contracts\Cache\CacheInterface $cache
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductFacade $bestsellingProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository
     */
    public function __construct(
        protected readonly CacheInterface $cache,
        protected readonly BestsellingProductFacade $bestsellingProductFacade,
        protected readonly ProductRepository $productRepository,
        protected readonly PricingGroupRepository $pricingGroupRepository,
    ) {
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return int[]
     */
    public function getAllOfferedBestsellingProductIds(
        int $domainId,
        Category $category,
        PricingGroup $pricingGroup,
    ): array {
        $cacheId = $this->getCacheId($domainId, $category, $pricingGroup);

        return $this->cache->get(
            $cacheId,
            function () use ($domainId, $category, $pricingGroup) {
                $bestsellingProducts = $this->bestsellingProductFacade->getAllOfferedBestsellingProducts(
                    $domainId,
                    $category,
                    $pricingGroup,
                );

                return array_map(
                    static function (Product $product): int {
                        return $product->getId();
                    },
                    $bestsellingProducts,
                );
            },
        );
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     */
    public function invalidateCacheByDomainIdAndCategory($domainId, Category $category)
    {
        $pricingGroups = $this->pricingGroupRepository->getPricingGroupsByDomainId($domainId);

        foreach ($pricingGroups as $pricingGroup) {
            $cacheId = $this->getCacheId($domainId, $category, $pricingGroup);
            $this->cache->delete($cacheId);
        }
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return string
     */
    protected function getCacheId($domainId, Category $category, PricingGroup $pricingGroup)
    {
        return $domainId . '_' . $category->getId() . '_' . $pricingGroup->getId();
    }
}

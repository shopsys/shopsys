<?php

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
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAllOfferedBestsellingProducts($domainId, Category $category, PricingGroup $pricingGroup)
    {
        $cacheId = $this->getCacheId($domainId, $category, $pricingGroup);

        $bestsellingProductIds = $this->cache->get(
            $cacheId,
            function () use ($domainId, $category, $pricingGroup) {
                $bestsellingProducts = $this->bestsellingProductFacade->getAllOfferedBestsellingProducts(
                    $domainId,
                    $category,
                    $pricingGroup,
                );

                return array_map(
                    static function (Product $product) {
                        return $product->getId();
                    },
                    $bestsellingProducts,
                );
            },
        );

        return $this->getSortedProducts($domainId, $pricingGroup, $bestsellingProductIds);
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
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int[] $sortedProductIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected function getSortedProducts($domainId, PricingGroup $pricingGroup, array $sortedProductIds)
    {
        return $this->productRepository->getOfferedByIds($domainId, $pricingGroup, $sortedProductIds);
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

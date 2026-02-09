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
    public function __construct(
        protected readonly CacheInterface $cache,
        protected readonly BestsellingProductFacade $bestsellingProductFacade,
        protected readonly ProductRepository $productRepository,
        protected readonly PricingGroupRepository $pricingGroupRepository,
    ) {
    }

    /**
     * @return int[]
     */
    public function getOfferedBestsellingProductIds(
        int $domainId,
        Category $category,
        PricingGroup $pricingGroup,
        int $limit,
    ): array {
        $cacheId = $this->getCacheId($domainId, $category, $pricingGroup);

        return $this->cache->get(
            $cacheId,
            function () use ($domainId, $category, $pricingGroup, $limit) {
                $bestsellingProducts = $this->bestsellingProductFacade->getOfferedBestsellingProducts(
                    $domainId,
                    $category,
                    $pricingGroup,
                    $limit,
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

    public function invalidateCacheByDomainIdAndCategory(int $domainId, Category $category): void
    {
        $pricingGroups = $this->pricingGroupRepository->getPricingGroupsByDomainId($domainId);

        foreach ($pricingGroups as $pricingGroup) {
            $cacheId = $this->getCacheId($domainId, $category, $pricingGroup);
            $this->cache->delete($cacheId);
        }
    }

    protected function getCacheId(int $domainId, Category $category, PricingGroup $pricingGroup): string
    {
        return $domainId . '_' . $category->getId() . '_' . $pricingGroup->getId();
    }
}

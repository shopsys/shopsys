<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class ProductVisibilityFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository $productVisibilityRepository
     */
    public function __construct(
        protected readonly ProductVisibilityRepository $productVisibilityRepository,
    ) {
    }

    /**
     * @param int[] $productIds
     */
    public function calculateProductVisibilityForIds(array $productIds): void
    {
        $this->productVisibilityRepository->refreshProductsVisibility($productIds);
    }

    public function calculateProductVisibilityForAll(): void
    {
        $this->productVisibilityRepository->refreshProductsVisibility();
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductVisibility[]
     */
    public function findProductVisibilitiesByDomainIdAndProduct(int $domainId, Product $product): array
    {
        return $this->productVisibilityRepository->findProductVisibilitiesByDomainIdAndProduct($domainId, $product);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param array<int, int> $defaultPricingGroupIdsIndexedByDomainId
     * @return bool
     */
    public function isProductVisibleOnAllDomains(
        Product $product,
        array $defaultPricingGroupIdsIndexedByDomainId,
    ): bool {
        $count = $this->productVisibilityRepository->getCountOfDomainsProductIsVisibleOn(
            $product,
            $defaultPricingGroupIdsIndexedByDomainId,
        );

        return $count === count($defaultPricingGroupIdsIndexedByDomainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param array<int, int> $defaultPricingGroupIdsIndexedByDomainId
     * @return bool
     */
    public function isProductVisibleOnSomeDomains(
        Product $product,
        array $defaultPricingGroupIdsIndexedByDomainId,
    ): bool {
        $count = $this->productVisibilityRepository->getCountOfDomainsProductIsVisibleOn(
            $product,
            $defaultPricingGroupIdsIndexedByDomainId,
        );

        return $count > 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductVisibility
     */
    public function getProductVisibility(
        Product $product,
        PricingGroup $pricingGroup,
        int $domainId,
    ): ProductVisibility {
        return $this->productVisibilityRepository->getProductVisibility($product, $pricingGroup, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int $domainId
     */
    public function createAndRefreshProductVisibilitiesForPricingGroup(PricingGroup $pricingGroup, int $domainId): void
    {
        $this->productVisibilityRepository->createAndRefreshProductVisibilitiesForPricingGroup($pricingGroup, $domainId);
    }
}

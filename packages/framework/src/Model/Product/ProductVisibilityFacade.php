<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class ProductVisibilityFacade
{
    public function __construct(
        protected readonly ProductVisibilityRepository $productVisibilityRepository,
        protected readonly Domain $domain,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
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
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductVisibility[]
     */
    public function findProductVisibilitiesByDomainIdAndProduct(int $domainId, Product $product): array
    {
        return $this->productVisibilityRepository->findProductVisibilitiesByDomainIdAndProduct($domainId, $product);
    }

    /**
     * @param array<int, int> $defaultPricingGroupIdsIndexedByDomainId
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
     * @param array<int, int> $defaultPricingGroupIdsIndexedByDomainId
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

    public function getProductVisibility(
        Product $product,
        PricingGroup $pricingGroup,
        int $domainId,
    ): ProductVisibility {
        return $this->productVisibilityRepository->getProductVisibility($product, $pricingGroup, $domainId);
    }

    public function createAndRefreshProductVisibilitiesForPricingGroup(PricingGroup $pricingGroup, int $domainId): void
    {
        $this->productVisibilityRepository->createAndRefreshProductVisibilitiesForPricingGroup($pricingGroup, $domainId);
    }

    /**
     * @param int[] $productIds
     * @return array<int, bool>
     */
    public function areProductsVisibleForDefaultPricingGroupOnSomeDomainIndexedByProductId(array $productIds): array
    {
        return $this->getProductsVisibilityIndexedByProductId($productIds, false);
    }

    /**
     * @param int[] $productIds
     * @return array<int, bool>
     */
    protected function getProductsVisibilityIndexedByProductId(array $productIds, bool $defaultVisibility): array
    {
        $productVisibilitiesIndexedByProductId = array_fill_keys($productIds, $defaultVisibility);

        foreach ($this->domain->getAllIds() as $domainId) {
            $productVisibilities = $this->getProductsVisibilitiesByDomainIndexedByProductId($productIds, $domainId);

            foreach ($productVisibilities as $productId => $productVisibility) {
                if ($defaultVisibility) {
                    $productVisibilitiesIndexedByProductId[$productId] = $productVisibilitiesIndexedByProductId[$productId]
                        && $productVisibility;
                } else {
                    $productVisibilitiesIndexedByProductId[$productId] = $productVisibilitiesIndexedByProductId[$productId]
                        || $productVisibility;
                }
            }
        }

        return $productVisibilitiesIndexedByProductId;
    }

    /**
     * @param int[] $productIds
     * @return array<int, bool>
     */
    public function areProductsVisibleForDefaultPricingGroupOnEachDomainIndexedByProductId(array $productIds): array
    {
        return $this->getProductsVisibilityIndexedByProductId($productIds, true);
    }

    /**
     * @param int[] $productIds
     * @return bool[]
     */
    protected function getProductsVisibilitiesByDomainIndexedByProductId(array $productIds, int $domainId): array
    {
        $pricingGroupId = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId)->getId();

        return $this->productVisibilityRepository->getProductsVisibilitiesByPricingGroupAndDomainIndexedByProductId(
            $productIds,
            $pricingGroupId,
            $domainId,
        );
    }
}

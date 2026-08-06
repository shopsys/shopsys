<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class ProductSellableVariantsProvider
{
    protected const string VARIANTS_CACHE_NAMESPACE = 'sellableVariantsForDefaultPricingGroup';

    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getVariantsForDefaultPricingGroup(Product $mainVariant, int $domainId): array
    {
        $defaultPricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);

        return $this->inMemoryCache->getOrSaveValue(
            static::VARIANTS_CACHE_NAMESPACE,
            fn () => $this->productRepository->getAllSellableVariantsByMainVariant(
                $mainVariant,
                $domainId,
                $defaultPricingGroup,
            ),
            $mainVariant->getId(),
            $defaultPricingGroup->getId(),
            $domainId,
        );
    }

    public function resetCache(): void
    {
        $this->inMemoryCache->deleteAllItemsInNamespace(static::VARIANTS_CACHE_NAMESPACE);
    }
}

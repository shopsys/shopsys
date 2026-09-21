<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\Preloader;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class OrderInputPreloaderFacade
{
    protected const string PRELOADED_ORDER_INPUTS_CACHE_NAMESPACE = 'preloadedOrderInputsByProductSet';

    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly ProductManualInputPriceRepository $productManualInputPriceRepository,
        protected readonly SpecialPriceFacade $specialPriceFacade,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    public function preload(OrderInput $orderInput): void
    {
        $productIds = $this->getProductIds($orderInput);

        if ($productIds === []) {
            return;
        }

        $preloadKey = $this->inMemoryCache->getKey(
            $orderInput->getDomainConfig()->getId(),
            $orderInput->getCustomerUser()?->getId() ?? 'anonymous',
            implode(',', $productIds),
        );

        if ($this->inMemoryCache->hasItem(static::PRELOADED_ORDER_INPUTS_CACHE_NAMESPACE, $preloadKey)) {
            return;
        }

        $this->preloadProductData($orderInput, $productIds);
        $this->inMemoryCache->save(static::PRELOADED_ORDER_INPUTS_CACHE_NAMESPACE, true, $preloadKey);
    }

    /**
     * @param int[] $productIds
     */
    protected function preloadProductData(OrderInput $orderInput, array $productIds): void
    {
        $domainId = $orderInput->getDomainConfig()->getId();
        $pricingGroup = $this->getPricingGroup($orderInput);

        $this->productRepository->preloadWithDomainsVatsAndTranslationsByIds($productIds);
        $this->productManualInputPriceRepository->preloadByProductIdsAndPricingGroup($productIds, $pricingGroup);
        $this->specialPriceFacade->preloadRelevantSpecialPricesByProductIds($productIds, $domainId);
        $this->productVisibilityFacade->preloadProductVisibilitiesByProductIds($productIds, $pricingGroup, $domainId);
    }

    protected function getPricingGroup(OrderInput $orderInput): PricingGroup
    {
        return $orderInput->getCustomerUser()?->getPricingGroup()
            ?? $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($orderInput->getDomainConfig()->getId());
    }

    /**
     * @return int[]
     */
    protected function getProductIds(OrderInput $orderInput): array
    {
        $productIds = array_map(
            static fn (QuantifiedProduct $quantifiedProduct): int => $quantifiedProduct->getProduct()->getId(),
            $orderInput->getQuantifiedProducts(),
        );
        $productIds = array_unique($productIds);
        sort($productIds);

        return $productIds;
    }
}

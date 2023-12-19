<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Model\Category\CategoryEvent;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyEvent;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupEvent;
use Shopsys\FrameworkBundle\Model\Product\AffectedProductsFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandEvent;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagEvent;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterEvent;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitEvent;
use Shopsys\FrameworkBundle\Model\Stock\StockEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DispatchAffectedProductsSubscriber implements EventSubscriberInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\AffectedProductsFacade $affectedProductsFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     * @param \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        protected readonly AffectedProductsFacade $affectedProductsFacade,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandEvent $brandEvent
     */
    public function dispatchAffectedByBrand(BrandEvent $brandEvent): void
    {
        $productIds = $this->affectedProductsFacade->getProductIdsWithBrand($brandEvent->getBrand());

        $this->productRecalculationDispatcher->dispatchProductIds($productIds);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryEvent $categoryEvent
     */
    public function dispatchAffectedByCategory(CategoryEvent $categoryEvent): void
    {
        $productIds = $this->affectedProductsFacade->getProductIdsWithCategory($categoryEvent->getCategory());

        $this->productRecalculationDispatcher->dispatchProductIds($productIds);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagEvent $flagEvent
     */
    public function dispatchAffectedByFlag(FlagEvent $flagEvent): void
    {
        $productIds = $this->affectedProductsFacade->getProductIdsWithFlag($flagEvent->getFlag());

        $this->productRecalculationDispatcher->dispatchProductIds($productIds);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterEvent $parameterEvent
     */
    public function dispatchAffectedByParameter(ParameterEvent $parameterEvent): void
    {
        $productIds = $this->affectedProductsFacade->getProductIdsWithParameter($parameterEvent->getParameter());

        $this->productRecalculationDispatcher->dispatchProductIds($productIds);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockEvent $stockEvent
     */
    public function dispatchAllProductsIfStockDomainsChanged(StockEvent $stockEvent): void
    {
        if ($stockEvent->hasChangedDomains()) {
            $this->dispatchAllProducts();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitEvent $unitEvent
     */
    public function dispatchAffectedByUnit(UnitEvent $unitEvent): void
    {
        $productIds = $this->affectedProductsFacade->getProductIdsWithUnit($unitEvent->getUnit());

        $this->productRecalculationDispatcher->dispatchProductIds($productIds);
    }

    public function dispatchAllProducts(): void
    {
        $this->productRecalculationDispatcher->dispatchAllProducts();
    }

    public function cleanStorefrontSettingsQueryCache(): void
    {
        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::SETTINGS_QUERY_KEY_PART);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BrandEvent::DELETE => 'dispatchAffectedByBrand',
            CategoryEvent::UPDATE => 'dispatchAffectedByCategory',
            CategoryEvent::DELETE => 'dispatchAffectedByCategory',
            CurrencyEvent::UPDATE => [
                ['dispatchAllProducts'],
                ['cleanStorefrontSettingsQueryCache'],
            ],
            FlagEvent::DELETE => 'dispatchAffectedByFlag',
            ParameterEvent::DELETE => 'dispatchAffectedByParameter',
            ParameterEvent::UPDATE => 'dispatchAffectedByParameter',
            PricingGroupEvent::CREATE => 'dispatchAllProducts',
            PricingGroupEvent::DELETE => 'dispatchAllProducts',
            StockEvent::DELETE => 'dispatchAllProducts',
            StockEvent::UPDATE => 'dispatchAllProductsIfStockDomainsChanged',
            UnitEvent::UPDATE => 'dispatchAffectedByUnit',
            UnitEvent::DELETE => 'dispatchAffectedByUnit',
        ];
    }
}

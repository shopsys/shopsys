<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexExportedEvent;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupEvent;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityEvent;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandEvent;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagEvent;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterEvent;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;

class MarkProductForExportSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    protected $productFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     */
    public function __construct(ProductFacade $productFacade)
    {
        $this->productFacade = $productFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityEvent $availabilityEvent
     */
    public function markAffectedByAvailability(AvailabilityEvent $availabilityEvent): void
    {
        $products = $this->productFacade->getProductsWithAvailability($availabilityEvent->getAvailability());
        $this->productFacade->markProductsForExport($products);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandEvent $brandEvent
     */
    public function markAffectedByBrand(BrandEvent $brandEvent): void
    {
        $productIds = $this->productFacade->getProductsWithBrand($brandEvent->getBrand());
        $this->productFacade->markProductsForExport($productIds);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagEvent $flagEvent
     */
    public function markAffectedByFlag(FlagEvent $flagEvent): void
    {
        $products = $this->productFacade->getProductsWithFlag($flagEvent->getFlag());
        $this->productFacade->markProductsForExport($products);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterEvent $parameterEvent
     */
    public function markAffectedByParameter(ParameterEvent $parameterEvent): void
    {
        $products = $this->productFacade->getProductsWithParameter($parameterEvent->getParameter());
        $this->productFacade->markProductsForExport($products);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitEvent $unitEvent
     */
    public function markAffectedByUnit(UnitEvent $unitEvent): void
    {
        $products = $this->productFacade->getProductsWithUnit($unitEvent->getUnit());
        $this->productFacade->markProductsForExport($products);
    }

    /**
     * @param \Symfony\Contracts\EventDispatcher\Event $event
     */
    public function markAll(Event $event): void
    {
        $this->productFacade->markAllProductsForExport();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexExportedEvent $indexExportedEvent
     */
    public function markAllAsExported(IndexExportedEvent $indexExportedEvent): void
    {
        if ($indexExportedEvent->getIndex() instanceof ProductIndex) {
            $this->productFacade->markAllProductsAsExported();
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ParameterEvent::DELETE => 'markAffectedByParameter',
            BrandEvent::DELETE => 'markAffectedByBrand',
            AvailabilityEvent::UPDATE => 'markAffectedByAvailability',
            AvailabilityEvent::DELETE => 'markAffectedByAvailability',
            UnitEvent::UPDATE => 'markAffectedByUnit',
            UnitEvent::DELETE => 'markAffectedByUnit',
            FlagEvent::DELETE => 'markAffectedByFlag',
            PricingGroupEvent::CREATE => 'markAll',
            PricingGroupEvent::DELETE => 'markAll',
            IndexExportedEvent::INDEX_EXPORTED => 'markAllAsExported',
        ];
    }
}

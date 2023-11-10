<?php

declare(strict_types=1);

namespace App\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\MarkProductForExportSubscriber as BaseMarkProductForExportSubscriber;
use Shopsys\FrameworkBundle\Model\Stock\StockEvent;

/**
 * @property \App\Model\Product\ProductFacade $productFacade
 * @method __construct(\App\Model\Product\ProductFacade $productFacade)
 */
class MarkProductForExportSubscriber extends BaseMarkProductForExportSubscriber
{
    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        $subscribedEvents = parent::getSubscribedEvents();

        $subscribedEvents[StockEvent::DELETE] = 'markAll';
        $subscribedEvents[StockEvent::UPDATE] = 'markAllIfStockDomainsChanged';

        return $subscribedEvents;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockEvent $stockEvent
     */
    public function markAllIfStockDomainsChanged(StockEvent $stockEvent): void
    {
        if ($stockEvent->hasChangedDomains()) {
            $this->markAll($stockEvent);
        }
    }
}

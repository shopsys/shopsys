<?php

declare(strict_types=1);

namespace App\Model\Product\Elasticsearch;

use App\Model\Stock\StockEvent;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\MarkProductForExportSubscriber as BaseMarkProductForExportSubscriber;

/**
 * @property \App\Model\Product\ProductFacade $productFacade
 * @method __construct(\App\Model\Product\ProductFacade $productFacade)
 */
class MarkProductForExportSubscriber extends BaseMarkProductForExportSubscriber
{
    /**
     * @return mixed[]
     */
    public static function getSubscribedEvents(): array
    {
        $subscribedEvents = parent::getSubscribedEvents();

        $subscribedEvents[StockEvent::DELETE] = 'markAll';
        $subscribedEvents[StockEvent::UPDATE] = 'markAllIfStockDomainsChanged';

        return $subscribedEvents;
    }

    /**
     * @param \App\Model\Stock\StockEvent $stockEvent
     */
    public function markAllIfStockDomainsChanged(StockEvent $stockEvent): void
    {
        if ($stockEvent->hasChangedDomains()) {
            $this->markAll($stockEvent);
        }
    }
}

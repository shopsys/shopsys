<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;

class OrderProcessingFacade
{
    protected const string PROCESSED_ORDER_DATA_CACHE_NAMESPACE = 'processedOrderDataByOrderInputFingerprint';

    public function __construct(
        protected readonly OrderProcessor $orderProcessor,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    public function getProcessedOrderData(OrderInput $orderInput): OrderData
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::PROCESSED_ORDER_DATA_CACHE_NAMESPACE,
            fn (): OrderData => $this->orderProcessor->process($orderInput, $this->orderDataFactory->create()),
            $orderInput->getFingerprint(),
        );
    }
}

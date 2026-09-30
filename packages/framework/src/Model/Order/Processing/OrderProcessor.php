<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;

class OrderProcessor
{
    protected const string PROCESSED_ORDER_DATA_CACHE_NAMESPACE = 'processedOrderDataByOrderInputFingerprint';

    public function __construct(
        protected readonly OrderProcessingStack $orderProcessingStack,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    public function processMemoized(OrderInput $orderInput): OrderData
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::PROCESSED_ORDER_DATA_CACHE_NAMESPACE,
            fn (): OrderData => $this->process($orderInput, $this->orderDataFactory->create()),
            $orderInput->getFingerprint(),
        );
    }

    /**
     * @template T of \Shopsys\FrameworkBundle\Model\Order\OrderData
     * @param T $orderData
     * @return T
     */
    public function process(
        OrderInput $orderInput,
        OrderData $orderData,
    ): OrderData {
        $orderData = clone $orderData;

        $orderProcessingData = new OrderProcessingData(
            $orderInput,
            $orderData,
        );

        $this->orderProcessingStack->rewind();

        $orderProcessingData = $this->orderProcessingStack->processNext($orderProcessingData);

        return $orderProcessingData->orderData;
    }
}

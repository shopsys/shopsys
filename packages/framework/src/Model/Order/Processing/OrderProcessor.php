<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Model\Order\OrderData;

class OrderProcessor
{
    public function __construct(
        protected readonly OrderProcessingStack $orderProcessingStack,
    ) {
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

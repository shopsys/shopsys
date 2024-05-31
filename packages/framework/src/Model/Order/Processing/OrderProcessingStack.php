<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

class OrderProcessingStack
{
    protected int $position = 0;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\OrderProcessorMiddlewareInterface[] $processingMiddlewares
     */
    public function __construct(
        protected iterable $processingMiddlewares,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
     */
    public function processNext(OrderProcessingData $orderProcessingData): OrderProcessingData
    {
        if ($this->hasNext()) {
            return $this->processingMiddlewares[$this->position++]->handle($orderProcessingData, $this);
        }

        return $orderProcessingData;
    }

    /**
     * @return bool
     */
    protected function hasNext(): bool
    {
        return $this->position < count($this->processingMiddlewares);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\OrderProcessorMiddlewareInterface;

class OrderProcessingStack
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\OrderProcessorMiddlewareInterface[] $processingMiddlewares
     */
    public function __construct(
        protected iterable $processingMiddlewares,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\OrderProcessorMiddlewareInterface
     */
    public function next(): OrderProcessorMiddlewareInterface
    {
        $value = current($this->processingMiddlewares);

        if (!$value) {
            throw new NoMoreMiddlewareInStackException();
        }

        next($this->processingMiddlewares);

        return $value;
    }

    public function rewind(): void
    {
        reset($this->processingMiddlewares);
    }
}

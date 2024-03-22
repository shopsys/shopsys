<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\OrderProcessorMiddlewareInterface;

class OrderProcessingStack implements OrderProcessingStackInterface
{
    /**
     * @param OrderProcessorMiddlewareInterface[] $processingMiddlewares
     */
    public function __construct(
        protected iterable $processingMiddlewares
    ) {
    }

    public function next(): OrderProcessorMiddlewareInterface
    {
        $value = current($this->processingMiddlewares);

        if (!$value) {
            throw new NoMoreMiddlewareInStackException();
        }

        next($this->processingMiddlewares);

        return $value;
    }
}

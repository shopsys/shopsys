<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\OrderProcessorMiddlewareInterface;

interface OrderProcessingStackInterface
{
    public function next(): OrderProcessorMiddlewareInterface;
}

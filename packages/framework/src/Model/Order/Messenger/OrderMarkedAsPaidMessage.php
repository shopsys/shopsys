<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Messenger;

class OrderMarkedAsPaidMessage
{
    public function __construct(
        public readonly int $orderId,
    ) {
    }
}

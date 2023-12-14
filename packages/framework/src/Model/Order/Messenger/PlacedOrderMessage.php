<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Messenger;

class PlacedOrderMessage
{
    /**
     * @param int $orderId
     */
    public function __construct(
        public readonly int $orderId,
    ) {
    }
}

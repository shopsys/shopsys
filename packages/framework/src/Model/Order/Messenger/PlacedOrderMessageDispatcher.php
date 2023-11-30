<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Messenger;

use Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher;

class PlacedOrderMessageDispatcher extends AbstractMessageDispatcher
{
    /**
     * @param int $orderId
     */
    public function dispatchPlacedOrderMessage(int $orderId): void
    {
        $this->messageBus->dispatch(new PlacedOrderMessage($orderId));
    }
}

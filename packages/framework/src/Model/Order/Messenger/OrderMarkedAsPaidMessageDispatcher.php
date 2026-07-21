<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Messenger;

use Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher;

class OrderMarkedAsPaidMessageDispatcher extends AbstractMessageDispatcher
{
    public function dispatchOrderMarkedAsPaidMessage(int $orderId): void
    {
        $this->messageBus->dispatch(new OrderMarkedAsPaidMessage($orderId));
    }
}

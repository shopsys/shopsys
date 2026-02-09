<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal\Messenger;

use Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher;

class WithdrawalRequestMessageDispatcher extends AbstractMessageDispatcher
{
    public function dispatchWithdrawalCreatedMessage(int $withdrawalId): void
    {
        $this->messageBus->dispatch(new WithdrawalRequestMessage($withdrawalId));
    }
}

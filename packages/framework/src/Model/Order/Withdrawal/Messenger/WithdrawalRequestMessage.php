<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal\Messenger;

class WithdrawalRequestMessage
{
    public function __construct(
        public readonly int $withdrawalRequestId,
    ) {
    }
}

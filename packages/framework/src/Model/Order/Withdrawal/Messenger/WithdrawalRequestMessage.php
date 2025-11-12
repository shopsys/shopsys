<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal\Messenger;

class WithdrawalRequestMessage
{
    /**
     * @param int $withdrawalRequestId
     */
    public function __construct(
        public readonly int $withdrawalRequestId,
    ) {
    }
}

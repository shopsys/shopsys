<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\OrderCancelledException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalAlreadyRequestedException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalDeadlinePassedException;

class WithdrawalChecker
{
    public function __construct(
        protected readonly WithdrawalDeadlineCalculation $withdrawalDeadlineCalculation,
        protected readonly WithdrawalRequestRepository $withdrawalRequestRepository,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function checkOrderWithdrawal(Order $order): void
    {
        if ($order->isCancelled()) {
            throw new OrderCancelledException('Withdrawal is not allowed for cancelled orders');
        }

        if ($this->withdrawalRequestRepository->findConfirmedByOrder($order) !== null) {
            throw new WithdrawalAlreadyRequestedException('Withdrawal has already been requested for this order');
        }

        $withdrawalDeadline = $this->withdrawalDeadlineCalculation->getWithdrawalDeadline($order);

        if ($withdrawalDeadline === null) {
            return;
        }

        if ($this->clock->now() > $withdrawalDeadline) {
            throw new WithdrawalDeadlinePassedException('Withdrawal deadline has passed for this order');
        }
    }
}

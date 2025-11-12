<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\OrderCancelledException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalAlreadyRequestedException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalDeadlinePassedException;

class WithdrawalChecker
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalDeadlineCalculation $withdrawalDeadlineCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestRepository $withdrawalRequestRepository
     */
    public function __construct(
        protected readonly WithdrawalDeadlineCalculation $withdrawalDeadlineCalculation,
        protected readonly OrderFacade $orderFacade,
        protected readonly Domain $domain,
        protected readonly WithdrawalRequestRepository $withdrawalRequestRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function checkOrderWithdrawal(Order $order): void
    {
        if ($order->isCancelled()) {
            throw new OrderCancelledException('Withdrawal is not allowed for cancelled orders');
        }

        if ($this->withdrawalRequestRepository->findByOrder($order) !== null) {
            throw new WithdrawalAlreadyRequestedException('Withdrawal has already been requested for this order');
        }

        $withdrawalDeadline = $this->withdrawalDeadlineCalculation->getWithdrawalDeadline($order);

        if ($withdrawalDeadline === null) {
            return;
        }

        $now = new DateTime();

        if ($now > $withdrawalDeadline) {
            throw new WithdrawalDeadlinePassedException('Withdrawal deadline has passed for this order');
        }
    }
}

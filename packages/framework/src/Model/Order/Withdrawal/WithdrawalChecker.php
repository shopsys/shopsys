<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\OrderCancelledException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\OrderNotFoundForWithdrawalException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalAlreadyRequestedException;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\WithdrawalDeadlinePassedException;

class WithdrawalChecker
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalDeadlineCalculation $withdrawalDeadlineCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly WithdrawalDeadlineCalculation $withdrawalDeadlineCalculation,
        protected readonly OrderFacade $orderFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param string $orderUrlHash
     */
    public function checkOrderWithdrawal(string $orderUrlHash): void
    {
        try {
            $order = $this->orderFacade->getByUrlHashAndDomain($orderUrlHash, $this->domain->getId());
        } catch (OrderNotFoundException) {
            throw new OrderNotFoundForWithdrawalException('Order not found');
        }

        if ($order->isCancelled()) {
            throw new OrderCancelledException('Withdrawal is not allowed for cancelled orders');
        }

        if ($order->getWithdrawalRequestedAt() !== null) {
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

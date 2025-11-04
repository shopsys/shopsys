<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;

class WithdrawalDeadlineCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalSetting $withdrawalSetting
     */
    public function __construct(
        protected readonly WithdrawalSetting $withdrawalSetting,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \DateTimeInterface|null
     */
    public function getWithdrawalDeadline(Order $order): ?DateTimeInterface
    {
        $deliveredAt = $order->getDeliveredAt();

        if ($deliveredAt === null) {
            return null;
        }

        $withdrawalDeadlineDays = $this->withdrawalSetting->getDeadlineDays($order->getDomainId());

        return (clone $deliveredAt)->modify(sprintf('+%d days', $withdrawalDeadlineDays));
    }
}

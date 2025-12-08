<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\BusinessDayCalculation;
use Shopsys\FrameworkBundle\Model\Order\Order;

class WithdrawalDeadlineCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalSetting $withdrawalSetting
     * @param \Shopsys\FrameworkBundle\Component\DateTimeHelper\BusinessDayCalculation $businessDayCalculation
     */
    public function __construct(
        protected readonly WithdrawalSetting $withdrawalSetting,
        protected readonly BusinessDayCalculation $businessDayCalculation,
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

        $deadline = $deliveredAt
            ->modify(sprintf('+%d days', $withdrawalDeadlineDays))
            ->setTime(0, 0);

        return $this->businessDayCalculation->getClosestBusinessDay($deadline, $order->getDomainId());
    }
}

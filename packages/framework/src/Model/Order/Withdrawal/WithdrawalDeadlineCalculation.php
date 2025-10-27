<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Order\Order;

class WithdrawalDeadlineCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        protected readonly Setting $setting,
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

        $withdrawalDeadlineDays = $this->setting->getForDomain(
            Setting::WITHDRAWAL_DEADLINE_DAYS,
            $order->getDomainId(),
        );

        return (clone $deliveredAt)->modify(sprintf('+%d days', $withdrawalDeadlineDays));
    }
}

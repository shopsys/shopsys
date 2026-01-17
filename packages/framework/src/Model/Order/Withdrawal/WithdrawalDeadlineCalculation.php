<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\BusinessDayCalculation;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;

class WithdrawalDeadlineCalculation
{
    public function __construct(
        protected readonly WithdrawalSetting $withdrawalSetting,
        protected readonly BusinessDayCalculation $businessDayCalculation,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
    ) {
    }

    public function getWithdrawalDeadline(Order $order): ?DateTimeInterface
    {
        $deliveredAt = $order->getDeliveredAt();

        if ($deliveredAt === null) {
            return null;
        }

        $domainId = $order->getDomainId();
        $withdrawalDeadlineDays = $this->withdrawalSetting->getDeadlineDays($domainId);

        $domainTimezone = $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainId);
        $deliveredAtInDomainTimezone = DateTimeImmutable::createFromInterface($deliveredAt)->setTimezone($domainTimezone);

        $deadline = $deliveredAtInDomainTimezone
            ->modify(sprintf('+%d days', $withdrawalDeadlineDays))
            ->setTime(23, 59, 59)
            ->setTimezone(new DateTimeZone('UTC'));

        return $this->businessDayCalculation->getClosestBusinessDay($deadline, $domainId);
    }
}

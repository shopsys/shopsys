<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DateTimeHelper;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;

class BusinessDayCalculation
{
    protected const int INCLUDE_HOLIDAYS_WITHIN_NEXT_DAYS_COUNT = 21;

    public function __construct(
        protected readonly ClosedDayFacade $closedDayFacade,
    ) {
    }

    public function getClosestBusinessDay(DateTimeImmutable $date, int $domainId): DateTimeImmutable
    {
        $endDate = $date->modify(sprintf('+%d days', self::INCLUDE_HOLIDAYS_WITHIN_NEXT_DAYS_COUNT));
        $publicHolidays = $this->closedDayFacade->getPublicHolidays(
            $domainId,
            $date,
            $endDate,
        );

        while (!$this->isBusinessDay($date, $publicHolidays)) {
            $date = $date->modify('+1 day');
        }

        return $date;
    }

    /**
     * @param \DateTimeInterface[] $publicHolidays
     */
    protected function isBusinessDay(DateTimeImmutable $date, array $publicHolidays): bool
    {
        if ($this->isWeekend($date)) {
            return false;
        }

        $dateString = $date->format('Y-m-d');

        foreach ($publicHolidays as $holiday) {
            if ($dateString === $holiday->format('Y-m-d')) {
                return false;
            }
        }

        return true;
    }

    protected function isWeekend(DateTimeImmutable $date): bool
    {
        return (int)$date->format('N') >= 6;
    }
}

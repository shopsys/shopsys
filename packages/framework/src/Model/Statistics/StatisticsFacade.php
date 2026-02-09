<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Statistics;

use DateInterval;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;

class StatisticsFacade
{
    public function __construct(
        protected readonly StatisticsRepository $statisticsRepository,
        protected readonly ValueByDateTimeDataPointFormatter $valueByDateTimeDataPointFormatter,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[]
     */
    public function getCustomersRegistrationsCountByDayInLastTwoWeeks(): array
    {
        $startDataTime = $this->clock->now()->modify('- 2 weeks midnight');
        $tomorrowDateTime = $this->clock->now()->modify('tomorrow');

        $valueByDateTimeDataPoints = $this->statisticsRepository->getCustomersRegistrationsCountByDayBetweenTwoDateTimes(
            $startDataTime,
            $tomorrowDateTime,
        );

        $valueByDateTimeDataPoints = $this->valueByDateTimeDataPointFormatter->normalizeDataPointsByDateTimeIntervals(
            $valueByDateTimeDataPoints,
            $startDataTime,
            $tomorrowDateTime,
            DateInterval::createFromDateString('+ 1 day'),
        );

        return $valueByDateTimeDataPoints;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[]
     */
    public function getNewOrdersCountByDayInLastTwoWeeks(): array
    {
        $startDataTime = $this->clock->now()->modify('- 2 weeks midnight');
        $tomorrowDateTime = $this->clock->now()->modify('tomorrow');

        $valueByDateTimeDataPoints = $this->statisticsRepository->getNewOrdersCountByDayBetweenTwoDateTimes(
            $startDataTime,
            $tomorrowDateTime,
        );

        $valueByDateTimeDataPoints = $this->valueByDateTimeDataPointFormatter->normalizeDataPointsByDateTimeIntervals(
            $valueByDateTimeDataPoints,
            $startDataTime,
            $tomorrowDateTime,
            DateInterval::createFromDateString('+ 1 day'),
        );

        return $valueByDateTimeDataPoints;
    }

    public function getNewCustomersCount(int $fromDays, ?int $toDays = null): int
    {
        $startDateTime = $this->clock->now()->modify('- ' . $fromDays . ' days');
        $endDateTime = $this->getToDateTime($toDays);

        return $this->statisticsRepository->getNewCustomersCountBetweenDates($startDateTime, $endDateTime);
    }

    public function getOrdersCount(int $fromDays, ?int $toDays = null): int
    {
        $startDateTime = $this->clock->now()->modify('- ' . $fromDays . ' days');
        $endDateTime = $this->getToDateTime($toDays);

        return $this->statisticsRepository->getOrdersCountBetweenDates($startDateTime, $endDateTime);
    }

    public function getOrdersValue(int $fromDays, ?int $toDays = null): int
    {
        $startDateTime = $this->clock->now()->modify('- ' . $fromDays . ' days');
        $endDateTime = $this->getToDateTime($toDays);

        return $this->statisticsRepository->getOrdersValueBetweenDates($startDateTime, $endDateTime);
    }

    protected function getToDateTime(?int $toDays = null): DateTimeImmutable
    {
        if ($toDays === null) {
            $endDateTime = $this->clock->now();
        } else {
            $endDateTime = $this->clock->now()->modify('- ' . $toDays . ' days');
        }

        return $endDateTime;
    }
}

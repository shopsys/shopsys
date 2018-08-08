<?php

namespace Shopsys\FrameworkBundle\Model\Statistics;

use DateInterval;
use DateTime;

class StatisticsFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Statistics\StatisticsRepository
     */
    protected $statisticsRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Statistics\StatisticsService
     */
    protected $statisticsService;

    public function __construct(
        StatisticsRepository $statisticsRepository,
        StatisticsService $statisticsService
    ) {
        $this->statisticsRepository = $statisticsRepository;
        $this->statisticsService = $statisticsService;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[]
     */
    public function getCustomersRegistrationsCountByDayInLastTwoWeeks(): array
    {
        $startDataTime = new DateTime('- 2 weeks midnight');
        $tomorrowDateTime = new DateTime('tomorrow');

        $valueByDateTimeDataPoints = $this->statisticsRepository->getCustomersRegistrationsCountByDayBetweenTwoDateTimes(
            $startDataTime,
            $tomorrowDateTime
        );

        $valueByDateTimeDataPoints = $this->statisticsService->normalizeDataPointsByDateTimeIntervals(
            $valueByDateTimeDataPoints,
            $startDataTime,
            $tomorrowDateTime,
            DateInterval::createFromDateString('+ 1 day')
        );

        return $valueByDateTimeDataPoints;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[]
     */
    public function getNewOrdersCountByDayInLastTwoWeeks(): array
    {
        $startDataTime = new DateTime('- 2 weeks midnight');
        $tomorrowDateTime = new DateTime('tomorrow');

        $valueByDateTimeDataPoints = $this->statisticsRepository->getNewOrdersCountByDayBetweenTwoDateTimes(
            $startDataTime,
            $tomorrowDateTime
        );

        $valueByDateTimeDataPoints = $this->statisticsService->normalizeDataPointsByDateTimeIntervals(
            $valueByDateTimeDataPoints,
            $startDataTime,
            $tomorrowDateTime,
            DateInterval::createFromDateString('+ 1 day')
        );

        return $valueByDateTimeDataPoints;
    }
}

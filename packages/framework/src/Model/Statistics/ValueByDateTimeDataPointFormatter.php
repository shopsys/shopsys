<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Statistics;

use DateInterval;
use DateTimeInterface;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;

class ValueByDateTimeDataPointFormatter
{
    public function __construct(protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[] $valueByDateTimeDataPoints
     * @return array
     */
    public function normalizeDataPointsByDateTimeIntervals(
        array $valueByDateTimeDataPoints,
        DateTimeInterface $startDateTime,
        DateTimeInterface $endDateTime,
        DateInterval $interval,
    ) {
        $currentProcessedDateTime = $startDateTime;
        $returnStatisticCounts = [];

        $dateTimes = $this->getDateTimes($valueByDateTimeDataPoints);

        do {
            $dateKey = array_search($currentProcessedDateTime, $dateTimes, false);

            if ($dateKey !== false) {
                $returnStatisticCounts[] = $valueByDateTimeDataPoints[$dateKey];
            } else {
                $returnStatisticCounts[] = new ValueByDateTimeDataPoint(0, $currentProcessedDateTime);
            }

            $currentProcessedDateTime = $currentProcessedDateTime->add($interval);
        } while ($currentProcessedDateTime < $endDateTime);

        return $returnStatisticCounts;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[] $valueByDateTimeDataPoints
     * @return string[]
     */
    public function getDateTimesFormattedToLocaleFormat(array $valueByDateTimeDataPoints)
    {
        $returnDates = [];

        foreach ($valueByDateTimeDataPoints as $valueByDateTimeDataPoint) {
            $returnDates[] = $this->dateTimeFormatterExtension->formatDate($valueByDateTimeDataPoint->getDateTime());
        }

        return $returnDates;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[] $valueByDateTimeDataPoints
     * @return \DateTimeInterface[]
     */
    protected function getDateTimes(array $valueByDateTimeDataPoints)
    {
        $returnData = [];

        foreach ($valueByDateTimeDataPoints as $key => $valueByDateTimeDataPoint) {
            $returnData[$key] = $valueByDateTimeDataPoint->getDateTime();
        }

        return $returnData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[] $valueByDateTimeDataPoints
     * @return int[]
     */
    public function getCounts(array $valueByDateTimeDataPoints)
    {
        $returnData = [];

        foreach ($valueByDateTimeDataPoints as $key => $valueByDateTimeDataPoint) {
            $returnData[$key] = $valueByDateTimeDataPoint->getValue();
        }

        return $returnData;
    }
}

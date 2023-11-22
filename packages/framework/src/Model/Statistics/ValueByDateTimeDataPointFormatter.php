<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Statistics;

use DateInterval;
use DateTime;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;

class ValueByDateTimeDataPointFormatter
{
    /**
     * @param \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension
     */
    public function __construct(protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[] $valueByDateTimeDataPoints
     * @param \DateTime $startDateTime
     * @param \DateTime $endDateTime
     * @param \DateInterval $interval
     * @return mixed[]
     */
    public function normalizeDataPointsByDateTimeIntervals(
        array $valueByDateTimeDataPoints,
        DateTime $startDateTime,
        DateTime $endDateTime,
        DateInterval $interval,
    ): array {
        $currentProcessedDateTime = $startDateTime;
        $returnStatisticCounts = [];

        $dateTimes = $this->getDateTimes($valueByDateTimeDataPoints);

        do {
            $dateKey = array_search($currentProcessedDateTime, $dateTimes, false);

            if ($dateKey !== false) {
                $returnStatisticCounts[] = $valueByDateTimeDataPoints[$dateKey];
            } else {
                $returnStatisticCounts[] = new ValueByDateTimeDataPoint(0, clone $currentProcessedDateTime);
            }

            $currentProcessedDateTime->add($interval);
        } while ($currentProcessedDateTime < $endDateTime);

        return $returnStatisticCounts;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[] $valueByDateTimeDataPoints
     * @return string[]
     */
    public function getDateTimesFormattedToLocaleFormat(array $valueByDateTimeDataPoints): array
    {
        $returnDates = [];

        foreach ($valueByDateTimeDataPoints as $valueByDateTimeDataPoint) {
            $returnDates[] = $this->dateTimeFormatterExtension->formatDate($valueByDateTimeDataPoint->getDateTime());
        }

        return $returnDates;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[] $valueByDateTimeDataPoints
     * @return \DateTime[]
     */
    protected function getDateTimes(array $valueByDateTimeDataPoints): array
    {
        $returnData = [];
        /** @var \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint $valueByDateTimeDataPoint */
        foreach ($valueByDateTimeDataPoints as $key => $valueByDateTimeDataPoint) {
            $returnData[$key] = $valueByDateTimeDataPoint->getDateTime();
        }

        return $returnData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[] $valueByDateTimeDataPoints
     * @return int[]
     */
    public function getCounts(array $valueByDateTimeDataPoints): array
    {
        $returnData = [];
        /** @var \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint $valueByDateTimeDataPoint */
        foreach ($valueByDateTimeDataPoints as $key => $valueByDateTimeDataPoint) {
            $returnData[$key] = $valueByDateTimeDataPoint->getValue();
        }

        return $returnData;
    }
}

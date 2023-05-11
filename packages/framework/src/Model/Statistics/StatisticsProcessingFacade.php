<?php

namespace Shopsys\FrameworkBundle\Model\Statistics;

class StatisticsProcessingFacade
{
    protected ValueByDateTimeDataPointFormatter $valueByDateTimeDataPointFormatter;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPointFormatter $valueByDateTimeDataPointFormatter
     */
    public function __construct(ValueByDateTimeDataPointFormatter $valueByDateTimeDataPointFormatter)
    {
        $this->valueByDateTimeDataPointFormatter = $valueByDateTimeDataPointFormatter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[] $valueByDateTimeDataPoints
     * @return string[]
     */
    public function getDateTimesFormattedToLocaleFormat(array $valueByDateTimeDataPoints)
    {
        return $this->valueByDateTimeDataPointFormatter->getDateTimesFormattedToLocaleFormat(
            $valueByDateTimeDataPoints
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Statistics\ValueByDateTimeDataPoint[] $valueByDateTimeDataPoints
     * @return int[]
     */
    public function getCounts(array $valueByDateTimeDataPoints)
    {
        return $this->valueByDateTimeDataPointFormatter->getCounts($valueByDateTimeDataPoints);
    }
}

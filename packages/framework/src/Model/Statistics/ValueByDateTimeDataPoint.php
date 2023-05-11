<?php

namespace Shopsys\FrameworkBundle\Model\Statistics;

use DateTime;

class ValueByDateTimeDataPoint
{
    protected int $value;

    protected DateTime $dateTime;

    /**
     * @param mixed $count
     * @param \DateTime $dateTime
     */
    public function __construct($count, DateTime $dateTime)
    {
        $this->value = (int)$count;
        $this->dateTime = $dateTime;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }
}

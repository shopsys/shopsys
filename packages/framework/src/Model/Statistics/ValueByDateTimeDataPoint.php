<?php

namespace Shopsys\FrameworkBundle\Model\Statistics;

use DateTime;

class ValueByDateTimeDataPoint
{
    /**
     * @var int
     */
    private $value;

    /**
     * @var \DateTime
     */
    private $dateTime;

    public function __construct($count, DateTime $dateTime)
    {
        $this->value = (int)$count;
        $this->dateTime = $dateTime;
    }

    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}

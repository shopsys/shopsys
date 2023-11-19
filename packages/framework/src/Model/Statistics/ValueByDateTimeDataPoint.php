<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Statistics;

use DateTime;

class ValueByDateTimeDataPoint
{
    protected int $value;

    /**
     * @param mixed $count
     * @param \DateTime $dateTime
     */
    public function __construct($count, protected readonly DateTime $dateTime)
    {
        $this->value = (int)$count;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }
}

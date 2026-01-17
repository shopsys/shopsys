<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Statistics;

use DateTimeInterface;

class ValueByDateTimeDataPoint
{
    protected int $value;

    /**
     * @param mixed $count
     */
    public function __construct($count, protected readonly DateTimeInterface $dateTime)
    {
        $this->value = (int)$count;
    }

    /**
     * @return \DateTimeInterface
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

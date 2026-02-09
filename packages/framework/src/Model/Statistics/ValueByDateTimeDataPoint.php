<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Statistics;

use DateTimeInterface;

class ValueByDateTimeDataPoint
{
    protected int $value;

    public function __construct(mixed $count, protected readonly DateTimeInterface $dateTime)
    {
        $this->value = (int)$count;
    }

    public function getDateTime(): DateTimeInterface
    {
        return $this->dateTime;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}

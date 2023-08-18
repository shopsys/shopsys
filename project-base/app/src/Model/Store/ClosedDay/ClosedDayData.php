<?php

declare(strict_types=1);

namespace App\Model\Store\ClosedDay;

use DateTimeImmutable;

class ClosedDayData
{
    /**
     * @var \App\Model\Store\Store[]
     */
    public array $excludedStores = [];

    public int $domainId;

    public DateTimeImmutable $date;

    public string $name;
}

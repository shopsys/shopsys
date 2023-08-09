<?php

declare(strict_types=1);

namespace App\Model\Store\ClosedDay;

use DateTime;

class ClosedDayData
{
    /**
     * @var \App\Model\Store\Store[]
     */
    public array $excludedStores = [];

    public int $domainId;

    public DateTime $date;

    public string $name;
}

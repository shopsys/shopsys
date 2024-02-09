<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

class OpeningHoursData
{
    /**
     * @var int|null
     */
    public $dayOfWeek;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeData[]
     */
    public $openingHoursRanges = [];
}

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
     * @var string|null
     */
    public $openingTime;

    /**
     * @var string|null
     */
    public $closingTime;
}

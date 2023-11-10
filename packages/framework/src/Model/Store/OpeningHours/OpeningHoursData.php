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
    public $firstOpeningTime;

    /**
     * @var string|null
     */
    public $firstClosingTime;

    /**
     * @var string|null
     */
    public $secondOpeningTime;

    /**
     * @var string|null
     */
    public $secondClosingTime;
}

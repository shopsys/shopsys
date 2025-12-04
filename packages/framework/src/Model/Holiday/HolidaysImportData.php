<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Holiday;

class HolidaysImportData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country
     */
    public $country;

    /**
     * @var int
     */
    public $year;

    /**
     * @var bool[]
     */
    public $selectedDomains;
}

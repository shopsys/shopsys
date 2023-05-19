<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

class AvailabilityData
{
    /**
     * @var string[]|null[]
     */
    public $name;

    /**
     * @var int|null
     */
    public $dispatchTime;

    public function __construct()
    {
        $this->name = [];
    }
}

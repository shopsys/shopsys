<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\ClosedDay;

class ClosedDayData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public $excludedStores = [];

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var \DateTimeImmutable|null
     */
    public $date;

    /**
     * @var string|null
     */
    public $name;
}

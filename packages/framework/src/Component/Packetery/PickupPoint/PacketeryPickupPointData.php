<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery\PickupPoint;

class PacketeryPickupPointData
{
    /**
     * @var string|null
     */
    public $id;

    /**
     * @var array<int, string[]>
     */
    public $regularOpeningHours = [];

    /**
     * @var array<int, string[]>|null
     */
    public $upcomingOpeningHours;

    /**
     * @var \DateTimeImmutable|null
     */
    public $upcomingOpeningHoursSince;

    /**
     * @var array<string, string[]>
     */
    public $exceptionOpeningHours = [];
}

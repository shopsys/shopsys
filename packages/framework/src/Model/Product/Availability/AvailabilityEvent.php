<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Symfony\Contracts\EventDispatcher\Event;

class AvailabilityEvent extends Event
{
    /**
     * The CREATE event occurs once a availability was created.
     *
     * This event allows you to run jobs dependent on the availability creation.
     */
    public const CREATE = 'availability.create';
    /**
     * The UPDATE event occurs once a availability was changed.
     *
     * This event allows you to run jobs dependent on the availability change.
     */
    public const UPDATE = 'availability.update';
    /**
     * The DELETE event occurs once a availability was deleted.
     *
     * This event allows you to run jobs dependent on the availability deletion.
     */
    public const DELETE = 'availability.delete';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    protected $availability;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $availability
     */
    public function __construct(Availability $availability)
    {
        $this->availability = $availability;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function getAvailability(): Availability
    {
        return $this->availability;
    }
}

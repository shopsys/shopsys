<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Symfony\Contracts\EventDispatcher\Event;

class UnitEvent extends Event
{
    /**
     * The CREATE event occurs once a unit was created.
     *
     * This event allows you to run jobs dependent on the unit creation.
     */
    public const CREATE = 'unit.create';
    /**
     * The UPDATE event occurs once a unit was changed.
     *
     * This event allows you to run jobs dependent on the unit change.
     */
    public const UPDATE = 'unit.update';
    /**
     * The DELETE event occurs once a unit was deleted.
     *
     * This event allows you to run jobs dependent on the unit deletion.
     */
    public const DELETE = 'unit.delete';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     */
    protected $unit;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $unit
     */
    public function __construct(Unit $unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     */
    public function getUnit(): Unit
    {
        return $this->unit;
    }
}

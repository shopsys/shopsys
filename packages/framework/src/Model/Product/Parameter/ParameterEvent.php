<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Symfony\Contracts\EventDispatcher\Event;

class ParameterEvent extends Event
{
    /**
     * The CREATE event occurs once a parameter was created.
     *
     * This event allows you to run jobs dependent on the parameter creation.
     */
    public const CREATE = 'parameter.create';
    /**
     * The UPDATE event occurs once a parameter was changed.
     *
     * This event allows you to run jobs dependent on the parameter change.
     */
    public const UPDATE = 'parameter.update';
    /**
     * The DELETE event occurs once a parameter was deleted.
     *
     * This event allows you to run jobs dependent on the parameter deletion.
     */
    public const DELETE = 'parameter.delete';

    public function __construct(protected readonly Parameter $parameter)
    {
    }

    public function getParameter(): Parameter
    {
        return $this->parameter;
    }
}

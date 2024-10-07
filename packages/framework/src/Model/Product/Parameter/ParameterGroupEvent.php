<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Symfony\Contracts\EventDispatcher\Event;

class ParameterGroupEvent extends Event
{
    /**
     * The CREATE event occurs once a parameter group was created.
     *
     * This event allows you to run jobs dependent on the parameter creation.
     */
    public const CREATE = 'parameterGroup.create';
    /**
     * The UPDATE event occurs once a parameter group was changed.
     *
     * This event allows you to run jobs dependent on the parameter change.
     */
    public const UPDATE = 'parameterGroup.update';
    /**
     * The DELETE event occurs once a parameter group was deleted.
     *
     * This event allows you to run jobs dependent on the parameter deletion.
     */
    public const DELETE = 'parameterGroup.delete';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup $parameterGroup
     */
    public function __construct(protected readonly ParameterGroup $parameterGroup)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup
     */
    public function getParameterGroup(): ParameterGroup
    {
        return $this->parameterGroup;
    }
}

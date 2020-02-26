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

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    protected $parameter;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     */
    public function __construct(Parameter $parameter)
    {
        $this->parameter = $parameter;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function getParameter(): Parameter
    {
        return $this->parameter;
    }
}

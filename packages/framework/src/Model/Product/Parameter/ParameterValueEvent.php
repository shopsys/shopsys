<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Symfony\Contracts\EventDispatcher\Event;

class ParameterValueEvent extends Event
{
    /**
     * The UPDATE event occurs once parameter values were changed.
     *
     * This event allows you to run jobs dependent on the parameter value change.
     */
    public const string UPDATE = 'parameter_value.update';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues
     */
    public function __construct(protected readonly array $parameterValues)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getParameterValues(): array
    {
        return $this->parameterValues;
    }
}

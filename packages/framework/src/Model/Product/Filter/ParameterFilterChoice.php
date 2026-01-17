<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

class ParameterFilterChoice
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $values
     */
    public function __construct(
        protected readonly Parameter $parameter,
        protected readonly array $values = [],
    ) {
    }

    public function getParameter(): Parameter
    {
        return $this->parameter;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}

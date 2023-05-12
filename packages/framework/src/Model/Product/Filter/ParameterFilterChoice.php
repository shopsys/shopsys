<?php

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

class ParameterFilterChoice
{
    protected Parameter $parameter;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $values
     */
    public function __construct(
        ?Parameter $parameter = null,
        protected readonly array $values = []
    ) {
        $this->parameter = $parameter;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getValues()
    {
        return $this->values;
    }
}

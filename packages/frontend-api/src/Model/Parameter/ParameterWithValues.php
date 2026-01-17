<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

class ParameterWithValues
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $values
     */
    public function __construct(protected readonly Parameter $parameter, protected readonly array $values)
    {
    }

    public function getParameter(): Parameter
    {
        return $this->parameter;
    }

    public function getUuid(): string
    {
        return $this->getParameter()->getUuid();
    }

    public function getName(): string
    {
        return $this->getParameter()->getName();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

class ParameterWithValues
{
    protected Parameter $parameter;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    protected array $values;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $values
     */
    public function __construct(Parameter $parameter, array $values)
    {
        $this->parameter = $parameter;
        $this->values = $values;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function getParameter(): Parameter
    {
        return $this->parameter;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->getParameter()->getUuid();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getParameter()->getName();
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->getParameter()->isVisible();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}

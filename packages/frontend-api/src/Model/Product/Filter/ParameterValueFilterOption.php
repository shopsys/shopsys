<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class ParameterValueFilterOption
{
    public ParameterValue $value;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue
     * @param int $count
     * @param bool $isAbsolute
     */
    public function __construct(
        ParameterValue $parameterValue,
        public readonly int $count,
        public readonly bool $isAbsolute,
    ) {
        $this->value = $parameterValue;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->value->getUuid();
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->value->getText();
    }
}

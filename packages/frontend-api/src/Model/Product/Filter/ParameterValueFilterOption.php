<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class ParameterValueFilterOption
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public ParameterValue $value;

    /**
     * @var int
     */
    public int $count;

    /**
     * @var bool
     */
    public bool $isAbsolute;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue
     * @param int $count
     * @param bool $isAbsolute
     */
    public function __construct(ParameterValue $parameterValue, int $count, bool $isAbsolute)
    {
        $this->value = $parameterValue;
        $this->count = $count;
        $this->isAbsolute = $isAbsolute;
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

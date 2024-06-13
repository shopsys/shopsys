<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class ParameterValueFilterOption
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue
     * @param int $count
     * @param bool $isAbsolute
     * @param bool $isSelected
     */
    public function __construct(
        public readonly ParameterValue $parameterValue,
        public readonly int $count,
        public readonly bool $isAbsolute,
        public readonly bool $isSelected,
    ) {
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->parameterValue->getUuid();
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->parameterValue->getText();
    }

    /**
     * @return string|null
     */
    public function getRgbHex(): ?string
    {
        return $this->parameterValue->getRgbHex();
    }
}

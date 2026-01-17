<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class ParameterValueFilterOption
{
    public function __construct(
        public readonly ParameterValue $parameterValue,
        public readonly int $count,
        public readonly bool $isAbsolute,
        public readonly bool $isSelected,
    ) {
    }

    public function getUuid(): string
    {
        return $this->parameterValue->getUuid();
    }

    public function getText(): string
    {
        return $this->parameterValue->getText();
    }

    public function getNumericValue(): ?string
    {
        return $this->parameterValue->getNumericValue();
    }

    public function getRgbHex(): ?string
    {
        return $this->parameterValue->getRgbHex();
    }
}

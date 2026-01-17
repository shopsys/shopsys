<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;

class ParameterFilterOption
{
    public ?float $minimalValue = null;

    public ?float $maximalValue = null;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterValueFilterOption[] $values
     */
    public function __construct(
        public readonly Parameter $parameter,
        public readonly array $values,
        public bool $isCollapsed,
        public bool $isSelectable,
        public ?float $selectedValue = null,
    ) {
        if ($parameter->isSlider()) {
            $floatValues = $this->getFloatValuesFromParameterValueFilterOptions($values);
            $this->minimalValue = min($floatValues);
            $this->maximalValue = max($floatValues);
        }
    }

    public function getUuid(): string
    {
        return $this->parameter->getUuid();
    }

    public function getName(): string
    {
        return $this->parameter->getName();
    }

    public function getUnit(): ?Unit
    {
        return $this->parameter->getUnit();
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterValueFilterOption[] $values
     * @return float[]
     */
    protected function getFloatValuesFromParameterValueFilterOptions(array $values): array
    {
        return array_map(static fn (ParameterValueFilterOption $parameterValueFilterOption) => (float)$parameterValueFilterOption->getNumericValue(), $values);
    }
}

<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use App\Model\Product\Unit\Unit;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter as BaseParameter;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterFilterOption as BaseParameterFilterOption;

/**
 * @property \App\FrontendApi\Model\Product\Filter\ParameterValueFilterOption[] $values
 */
class ParameterFilterOption extends BaseParameterFilterOption
{
    public ?float $minimalValue = null;

    public ?float $maximalValue = null;

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param \App\FrontendApi\Model\Product\Filter\ParameterValueFilterOption[] $values
     * @param bool $isCollapsed
     * @param float|null $selectedValue
     */
    public function __construct(
        BaseParameter $parameter,
        array $values,
        public bool $isCollapsed,
        public ?float $selectedValue = null,
    ) {
        parent::__construct($parameter, $values);

        if (!$parameter->isSlider()) {
            return;
        }
        $floatValues = $this->getFloatValuesFromParameterValueFilterOptions($values);
        $this->minimalValue = min($floatValues);
        $this->maximalValue = max($floatValues);
    }

    /**
     * @return \App\Model\Product\Unit\Unit|null
     */
    public function getUnit(): ?Unit
    {
        return $this->parameter->getUnit();
    }

    /**
     * @param \App\FrontendApi\Model\Product\Filter\ParameterValueFilterOption[] $values
     * @return float[]
     */
    private function getFloatValuesFromParameterValueFilterOptions(array $values): array
    {
        return array_map(static fn (ParameterValueFilterOption $parameterValueFilterOption) => (float)$parameterValueFilterOption->getText(), $values);
    }
}

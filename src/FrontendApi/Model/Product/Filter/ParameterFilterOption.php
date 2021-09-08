<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use App\Model\Product\Unit\Unit;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter as BaseParameter;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterFilterOption as BaseParameterFilterOption;

/**
 * @property \App\FrontendApi\Model\Product\Filter\ParameterValueFilterOption[] $values
 * @method __construct(\App\Model\Product\Parameter\Parameter $parameter, \App\FrontendApi\Model\Product\Filter\ParameterValueFilterOption[] $values)
 */
class ParameterFilterOption extends BaseParameterFilterOption
{
    /**
     * @var \App\Model\Product\Parameter\Parameter
     */
    public BaseParameter $parameter;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->parameter->getParameterType();
    }

    /**
     * @return \App\Model\Product\Unit\Unit|null
     */
    public function getUnit(): ?Unit
    {
        return $this->parameter->getUnit();
    }
}

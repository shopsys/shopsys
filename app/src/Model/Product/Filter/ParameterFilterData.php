<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData as BaseParameterFilterData;

/**
 * @property \App\Model\Product\Parameter\Parameter|null $parameter
 * @property \App\Model\Product\Parameter\ParameterValue[] $values
 */
class ParameterFilterData extends BaseParameterFilterData
{
    /**
     * @var bool
     */
    public $parameterFilteredBySlider = false;
}

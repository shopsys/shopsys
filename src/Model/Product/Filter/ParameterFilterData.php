<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData as BaseParameterFilterData;

class ParameterFilterData extends BaseParameterFilterData
{
    /**
     * @var bool
     */
    public $parameterFilteredBySlider = false;
}

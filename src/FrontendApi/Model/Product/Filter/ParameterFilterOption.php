<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter as BaseParameter;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterFilterOption as BaseParameterFilterOption;

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
}

<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterValueFilterOption as BaseParameterValueFilterOption;

/**
 * @method __construct(\App\Model\Product\Parameter\ParameterValue $parameterValue, int $count, bool $isAbsolute)
 */
class ParameterValueFilterOption extends BaseParameterValueFilterOption
{
    /**
     * @var \App\Model\Product\Parameter\ParameterValue
     */
    public ParameterValue $value;

    /**
     * @return string|null
     */
    public function getRgbHex(): ?string
    {
        return $this->value->getRgbHex();
    }
}

<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Parameter;

use App\Model\Product\Unit\Unit;
use Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValues as BaseParameterWithValues;

/**
 * @property \App\Model\Product\Parameter\Parameter $parameter
 * @property \App\Model\Product\Parameter\ParameterValue[] $values
 * @method __construct(\App\Model\Product\Parameter\Parameter $parameter, \App\Model\Product\Parameter\ParameterValue[] $values)
 * @method \App\Model\Product\Parameter\Parameter getParameter()
 * @method \App\Model\Product\Parameter\ParameterValue[] getValues()
 */
class ParameterWithValues extends BaseParameterWithValues
{
    /**
     * @return string|null
     */
    public function getGroup(): ?string
    {
        $group = $this->parameter->getGroup();

        if ($group === null) {
            return null;
        }

        return $group->getName();
    }

    /**
     * @return \App\Model\Product\Unit\Unit|null
     */
    public function getUnit(): ?Unit
    {
        return $this->parameter->getUnit();
    }
}

<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter as BaseParameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData as BaseParameterData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactory as BaseParameterDataFactory;

/**
 * @method \App\Model\Product\Parameter\ParameterData create()
 * @method \App\Model\Product\Parameter\ParameterData createFromParameter(\App\Model\Product\Parameter\Parameter $parameter)
 * @method fillNew(\App\Model\Product\Parameter\ParameterData $parameterData)
 */
class ParameterDataFactory extends BaseParameterDataFactory
{
    /**
     * @return \App\Model\Product\Parameter\ParameterData
     */
    protected function createInstance(): BaseParameterData
    {
        return new ParameterData();
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterData $parameterData
     * @param \App\Model\Product\Parameter\Parameter $parameter
     */
    protected function fillFromParameter(BaseParameterData $parameterData, BaseParameter $parameter)
    {
        parent::fillFromParameter($parameterData, $parameter);

        $parameterData->group = $parameter->getGroup();
    }
}

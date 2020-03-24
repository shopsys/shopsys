<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter as BaseParameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData as BaseParameterData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactory as BaseParameterDataFactory;

class ParameterDataFactory extends BaseParameterDataFactory
{
    /**
     * @return \App\Model\Product\Parameter\ParameterData
     */
    public function create(): BaseParameterData
    {
        $parameterData = new ParameterData();
        $this->fillNew($parameterData);

        return $parameterData;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterData $parameterData
     */
    protected function fillNew(BaseParameterData $parameterData): void
    {
        parent::fillNew($parameterData);

        $parameterData->orderingPriority = 0;
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @return \App\Model\Product\Parameter\ParameterData
     */
    public function createFromParameter(BaseParameter $parameter): BaseParameterData
    {
        $parameterData = new ParameterData();
        $this->fillFromParameter($parameterData, $parameter);

        return $parameterData;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterData $parameterData
     * @param \App\Model\Product\Parameter\Parameter $parameter
     */
    protected function fillFromParameter(BaseParameterData $parameterData, BaseParameter $parameter)
    {
        parent::fillFromParameter($parameterData, $parameter);

        $parameterData->group = $parameter->getGroup();
        $parameterData->orderingPriority = $parameter->getOrderingPriority();
        $parameterData->akeneoCode = $parameter->getAkeneoCode();
        $parameterData->akeneoType = $parameter->getAkeneoType();
    }
}

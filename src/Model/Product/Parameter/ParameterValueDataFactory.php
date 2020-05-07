<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue as BaseParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData as BaseParameterValueData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory as BaseParameterValueDataFactory;

class ParameterValueDataFactory extends BaseParameterValueDataFactory
{
    /**
     * @return \App\Model\Product\Parameter\ParameterValueData
     */
    public function create(): BaseParameterValueData
    {
        $parameterValueData = new ParameterValueData();
        $this->fillNew($parameterValueData);

        return $parameterValueData;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterValueData $parameterValueData
     */
    protected function fillNew(BaseParameterValueData $parameterValueData): void
    {
        $parameterValueData->unit = null;
        $parameterValueData->rgbHex = null;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterValue $parameterValue
     * @return \App\Model\Product\Parameter\ParameterValueData
     */
    public function createFromParameterValue(BaseParameterValue $parameterValue): BaseParameterValueData
    {
        $parameterValueData = new ParameterValueData();
        $this->fillFromParameterValue($parameterValueData, $parameterValue);

        return $parameterValueData;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterValueData $parameterValueData
     * @param \App\Model\Product\Parameter\ParameterValue $parameterValue
     */
    protected function fillFromParameterValue(BaseParameterValueData $parameterValueData, BaseParameterValue $parameterValue)
    {
        parent::fillFromParameterValue($parameterValueData, $parameterValue);

        $parameterValueData->unit = $parameterValue->getUnit();
        $parameterValueData->rgbHex = $parameterValue->getRgbHex();
    }
}

<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ParameterValueDataFactory implements ParameterValueDataFactoryInterface
{
    public function create(): ParameterValueData
    {
        return new ParameterValueData();
    }

    public function createFromParameterValue(ParameterValue $parameterValue): ParameterValueData
    {
        $parameterValueData = new ParameterValueData();
        $this->fillFromParameterValue($parameterValueData, $parameterValue);

        return $parameterValueData;
    }

    protected function fillFromParameterValue(ParameterValueData $parameterValueData, ParameterValue $parameterValue): void
    {
        $parameterValueData->text = $parameterValue->getText();
        $parameterValueData->locale = $parameterValue->getLocale();
    }
}

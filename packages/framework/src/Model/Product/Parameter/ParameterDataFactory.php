<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ParameterDataFactory implements ParameterDataFactoryInterface
{
    public function create(): ParameterData
    {
        return new ParameterData();
    }

    public function createFromParameter(Parameter $parameter): ParameterData
    {
        $parameterData = new ParameterData();
        $this->fillFromParameter($parameterData, $parameter);

        return $parameterData;
    }

    protected function fillFromParameter(ParameterData $parameterData, Parameter $parameter)
    {
        $translations = $parameter->getTranslations();
        $names = [];
        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $parameterData->name = $names;
        $parameterData->visible = $parameter->isVisible();
    }
}

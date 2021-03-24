<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

class ParameterValuesViewFactory
{
    /**
     * @param array $parameterArray
     * @return \App\Model\Product\Parameter\ParameterValuesViewData[]
     */
    public function getDimensionParametersFromArray(array $parameterArray): array
    {
        $result = [];
        foreach ($parameterArray as $parameter) {
            if ($parameter['parameter_is_dimensional'] === true) {
                $result[] = $this->createParameterValueViewData($parameter);
            }
        }

        return $result;
    }

    /**
     * @param array $parameterArray
     * @return \App\Model\Product\Parameter\ParameterValuesViewData[]
     */
    public function getNonDimensionParametersFromArray(array $parameterArray): array
    {
        $result = [];
        foreach ($parameterArray as $parameter) {
            if ($parameter['parameter_is_dimensional'] === false) {
                $result[] = $this->createParameterValueViewData($parameter);
            }
        }

        return $result;
    }

    /**
     * @param array $parameter
     * @return \App\Model\Product\Parameter\ParameterValuesViewData
     */
    protected function createParameterValueViewData(array $parameter): ParameterValuesViewData
    {
        $viewData = new ParameterValuesViewData(
            $parameter['parameter_name'],
            null,
            null,
            null
        );
        $viewData->addParameterValueText($parameter['parameter_value_text']);
        return $viewData;
    }
}

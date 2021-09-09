<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter as BaseParameter;
use Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory as BaseParameterWithValuesFactory;

/**
 * @method \App\FrontendApi\Model\Parameter\ParameterWithValues create(\App\Model\Product\Parameter\Parameter $parameter, \App\Model\Product\Parameter\ParameterValue[] $parameterValues)
 * @method \App\FrontendApi\Model\Parameter\ParameterWithValues[] createMultipleForProduct(\App\Model\Product\Product $product)
 */
class ParameterWithValuesFactory extends BaseParameterWithValuesFactory
{
    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param \App\Model\Product\Parameter\ParameterValue[] $parameterValues
     * @return \App\FrontendApi\Model\Parameter\ParameterWithValues
     */
    public function create(BaseParameter $parameter, array $parameterValues): ParameterWithValues
    {
        return new ParameterWithValues($parameter, $parameterValues);
    }

    /**
     * @param array $productData
     * @return array
     */
    public function createParametersArrayFromProductArray(array $productData): array
    {
        $parametersWithValues = [];

        foreach ($productData['parameters'] as $parameterArray) {
            $parameterUuid = $parameterArray['parameter_uuid'];

            if (!array_key_exists($parameterUuid, $parametersWithValues)) {
                $parametersWithValues[$parameterUuid] = $this->mapParameterArray($parameterArray);
            }

            $parametersWithValues[$parameterUuid]['values'][] = [
                'uuid' => $parameterArray['parameter_value_uuid'],
                'text' => $parameterArray['parameter_value_text'],
            ];
        }

        return $parametersWithValues;
    }

    /**
     * @param array $product
     * @return array
     */
    protected function mapParameterArray(array $product): array
    {
        return [
            'uuid' => $product['parameter_uuid'],
            'name' => $product['parameter_name'],
            'visible' => true,
            'group' => $product['parameter_group'],
            'unit' => $product['parameter_unit'] ? ['name' => $product['parameter_unit']] : null,
            'values' => [],
        ];
    }
}

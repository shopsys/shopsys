<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Parameter;

use Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory as BaseParameterWithValuesFactory;

/**
 * @method \Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValues create(\Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues)
 * @method \Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValues[] createMultipleForProduct(\App\Model\Product\Product $product)
 */
class ParameterWithValuesFactory extends BaseParameterWithValuesFactory
{
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
            'group' => $product['parameter_group'],
            'unit' => $product['parameter_unit'] ? ['name' => $product['parameter_unit']] : null,
            'values' => [],
        ];
    }
}

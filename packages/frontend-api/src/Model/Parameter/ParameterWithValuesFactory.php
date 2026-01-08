<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;

class ParameterWithValuesFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     */
    public function __construct(
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues
     * @return \Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValues
     */
    public function create(Parameter $parameter, array $parameterValues): ParameterWithValues
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
            'group' => $product['parameter_group'],
            'unit' => $product['parameter_unit'] ? ['name' => $product['parameter_unit']] : null,
            'values' => [],
        ];
    }
}

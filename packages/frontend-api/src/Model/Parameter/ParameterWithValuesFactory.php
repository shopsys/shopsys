<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;

class ParameterWithValuesFactory
{
    public function __construct(
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues
     */
    public function create(Parameter $parameter, array $parameterValues): ParameterWithValues
    {
        return new ParameterWithValues($parameter, $parameterValues);
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValues[]
     */
    public function createMultipleForProduct(Product $product): array
    {
        $productParameterValues = $this->productCachedAttributesFacade->getProductParameterValues($product);

        $valuesByParameterId = [];
        $parameters = [];
        $parametersWithValues = [];

        foreach ($productParameterValues as $productParameterValue) {
            $parameterId = $productParameterValue->getParameter()->getId();

            if (!array_key_exists($parameterId, $valuesByParameterId)) {
                $valuesByParameterId[$parameterId] = [];
            }

            array_push($valuesByParameterId[$parameterId], $productParameterValue->getValue());
            $parameters[$parameterId] = $productParameterValue->getParameter();
        }

        foreach ($parameters as $parameter) {
            $parametersWithValues[] = $this->create($parameter, $valuesByParameterId[$parameter->getId()]);
        }

        return $parametersWithValues;
    }

    public function createParametersArrayFromProductArray(array $productData): array
    {
        $parametersWithValues = [];

        foreach ($productData['parameters'] as $parameterArray) {
            $parametersWithValues[] = $this->mapParameterArray($parameterArray);
        }

        return $parametersWithValues;
    }

    protected function mapParameterArray(array $product): array
    {
        return [
            'uuid' => $product['parameter_uuid'],
            'name' => $product['parameter_name'],
            'unit' => $product['parameter_unit'] ? ['name' => $product['parameter_unit']] : null,
            'values' => [[
                'uuid' => $product['parameter_value_uuid'],
                'text' => $product['parameter_value_text'],
            ]],
        ];
    }
}

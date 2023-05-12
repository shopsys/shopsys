<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;

class ParameterWithValuesFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     */
    public function __construct(
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
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

    /**
     * @param array $productData
     * @return array
     */
    public function createParametersArrayFromProductArray(array $productData): array
    {
        $parametersWithValues = [];

        foreach ($productData['parameters'] as $parameterArray) {
            $parametersWithValues[] = $this->mapParameterArray($parameterArray);
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
            'values' => [[
                'uuid' => $product['parameter_value_uuid'],
                'text' => $product['parameter_value_text'],
            ]],
        ];
    }
}

<?php
declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer as BaseProductFilterDataToQueryTransformer;

class ProductFilterDataToQueryTransformer extends BaseProductFilterDataToQueryTransformer
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData[] $parameters
     * @return array
     */
    protected function flattenParameterFilterData(array $parameters): array
    {
        $result = parent::flattenParameterFilterData($parameters);

        foreach ($parameters as $parameterFilterData) {
            if ($parameterFilterData->parameterFilteredBySlider === true && count($parameterFilterData->values) === 0) {
                $result[$parameterFilterData->parameter->getId()] = [];
            }
        }

        return $result;
    }
}

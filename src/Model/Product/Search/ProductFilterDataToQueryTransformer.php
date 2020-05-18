<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer as BaseProductFilterDataToQueryTransformer;

/**
 * @method \App\Model\Product\Search\FilterQuery addBrandsToQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \App\Model\Product\Search\FilterQuery addFlagsToQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \App\Model\Product\Search\FilterQuery addParametersToQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \App\Model\Product\Search\FilterQuery addStockToQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \App\Model\Product\Search\FilterQuery addPricesToQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 */
class ProductFilterDataToQueryTransformer extends BaseProductFilterDataToQueryTransformer
{
    /**
     * @param \App\Model\Product\Filter\ParameterFilterData[] $parameters
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

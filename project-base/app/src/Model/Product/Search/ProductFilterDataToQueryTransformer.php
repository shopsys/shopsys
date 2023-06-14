<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer as BaseProductFilterDataToQueryTransformer;

/**
 * @method \App\Model\Product\Search\FilterQuery addBrandsToQuery(\App\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \App\Model\Product\Search\FilterQuery addFlagsToQuery(\App\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \App\Model\Product\Search\FilterQuery addStockToQuery(\App\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \App\Model\Product\Search\FilterQuery addPricesToQuery(\App\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method array flattenParameterFilterData(\App\Model\Product\Filter\ParameterFilterData[] $parameters)
 */
class ProductFilterDataToQueryTransformer extends BaseProductFilterDataToQueryTransformer
{
    /**
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \App\Model\Product\Search\FilterQuery $filterQuery
     * @return \App\Model\Product\Search\FilterQuery
     */
    public function addParametersToQuery(ProductFilterData $productFilterData, FilterQuery $filterQuery): FilterQuery
    {
        $parametersFilterData = $productFilterData->parameters;

        if (count($parametersFilterData) === 0) {
            return $filterQuery;
        }

        $parameters = $this->flattenParameterFilterData($parametersFilterData);
        $sliderParametersData = $this->getSliderParametersData($parametersFilterData);

        return $filterQuery
            ->filterByParameters($parameters)
            ->filterBySliderParameters($sliderParametersData);
    }

    /**
     * @param \App\Model\Product\Filter\ParameterFilterData[] $parametersFilterData
     * @return \App\Model\Product\Filter\ParameterFilterData[]
     */
    private function getSliderParametersData(array $parametersFilterData): array
    {
        foreach ($parametersFilterData as $key => $parameterFilterData) {
            $parameter = $parameterFilterData->parameter;

            if ($parameter === null || $parameter->isSlider() === false) {
                unset($parametersFilterData[$key]);
            }
        }

        return $parametersFilterData;
    }
}

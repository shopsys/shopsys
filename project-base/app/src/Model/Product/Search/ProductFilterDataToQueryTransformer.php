<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer as BaseProductFilterDataToQueryTransformer;

/**
 * @method \App\Model\Product\Search\FilterQuery addBrandsToQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \App\Model\Product\Search\FilterQuery addFlagsToQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \App\Model\Product\Search\FilterQuery addStockToQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \App\Model\Product\Search\FilterQuery addPricesToQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 */
class ProductFilterDataToQueryTransformer extends BaseProductFilterDataToQueryTransformer
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
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
}

<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery as BaseFilterQuery;

/**
 * @method \App\Model\Product\Search\FilterQuery applyOrdering(string $orderingModeId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \App\Model\Product\Search\FilterQuery filterByParameters(array $parameters)
 * @method \App\Model\Product\Search\FilterQuery filterByCategory(int $categoryId)
 * @method \App\Model\Product\Search\FilterQuery filterByBrands(int[] $brandIds)
 * @method \App\Model\Product\Search\FilterQuery filterByFlags(int[] $flagIds)
 * @method \App\Model\Product\Search\FilterQuery filterOnlyInStock()
 * @method \App\Model\Product\Search\FilterQuery filterOnlyVisible(\Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \App\Model\Product\Search\FilterQuery setPage(int $page)
 * @method \App\Model\Product\Search\FilterQuery setLimit(int $limit)
 * @method \App\Model\Product\Search\FilterQuery setFrom(int $from)
 * @method \App\Model\Product\Search\FilterQuery filterByProductIds(int[] $productIds)
 * @method \App\Model\Product\Search\FilterQuery filterByProductUuids(string[] $productUuids)
 * @method \App\Model\Product\Search\FilterQuery filterOutVariants()
 * @method \App\Model\Product\Search\FilterQuery restrictFields(string[] $fields)
 * @method \App\Model\Product\Search\FilterQuery filterBySliderParameters(\Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData[] $sliderParametersData)
 * @method \App\Model\Product\Search\FilterQuery filterByPrices(\Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, \Shopsys\FrameworkBundle\Component\Money\Money|null $minimalPrice = null, \Shopsys\FrameworkBundle\Component\Money\Money|null $maximalPrice = null)
 * @method \App\Model\Product\Search\FilterQuery applyOrderingByIdAscending()
 * @method \App\Model\Product\Search\FilterQuery filterOnlySellable()
 * @method \App\Model\Product\Search\FilterQuery filterBySellingFrom(\DateTimeImmutable $sellingFrom)
 * @method \App\Model\Product\Search\FilterQuery simpleSearch(string $text)
 * @method \App\Model\Product\Search\FilterQuery search(string $text)
 * @method \App\Model\Product\Search\FilterQuery filterOnlyInStockOrAllowedNegativeStock()
 * @method \App\Model\Product\Search\FilterQuery applyOrderingByIdsArray(int[] $ids)
 * @method \App\Model\Product\Search\FilterQuery filterWithActiveSpecialPriceOnly(\Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 */
class FilterQuery extends BaseFilterQuery
{
}

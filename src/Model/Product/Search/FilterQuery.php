<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery as BaseFilterQuery;

/**
 * @method \App\Model\Product\Search\FilterQuery applyOrdering(string $orderingModeId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \App\Model\Product\Search\FilterQuery applyDefaultOrdering()
 * @method \App\Model\Product\Search\FilterQuery filterByParameters(array $parameters)
 * @method \App\Model\Product\Search\FilterQuery filterByPrices(\Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, \Shopsys\FrameworkBundle\Component\Money\Money|null $minimalPrice, \Shopsys\FrameworkBundle\Component\Money\Money|null $maximalPrice)
 * @method \App\Model\Product\Search\FilterQuery filterByCategory(int[] $categoryIds)
 * @method \App\Model\Product\Search\FilterQuery filterByBrands(int[] $brandIds)
 * @method \App\Model\Product\Search\FilterQuery filterByFlags(int[] $flagIds)
 * @method \App\Model\Product\Search\FilterQuery filterOnlyInStock()
 * @method \App\Model\Product\Search\FilterQuery filterOnlySellable()
 * @method \App\Model\Product\Search\FilterQuery filterOnlyVisible(\Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \App\Model\Product\Search\FilterQuery setPage(int $page)
 * @method \App\Model\Product\Search\FilterQuery setLimit(int $limit)
 * @method \App\Model\Product\Search\FilterQuery setFrom(int $from)
 */
class FilterQuery extends BaseFilterQuery
{
    /**
     * @param string $text
     * @return \App\Model\Product\Search\FilterQuery
     */
    public function search(string $text): BaseFilterQuery
    {
        /** @var \App\Model\Product\Search\FilterQuery $clonedQuery */
        $clonedQuery = parent::search($text);

        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix.full_with_diacritic^60';
        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix.full_without_diacritic^50';
        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix^45';
        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix.edge_ngram_with_diacritic^40';
        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix.edge_ngram_without_diacritic^35';

        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix.full_with_diacritic^60';
        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix.full_without_diacritic^50';
        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix^45';
        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix.edge_ngram_with_diacritic^40';
        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix.edge_ngram_without_diacritic^35';

        return $clonedQuery;
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): array
    {
        $query = parent::getQuery();
        unset($query['type']);

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function getAbsoluteNumbersAggregationQuery(): array
    {
        $query = parent::getAbsoluteNumbersAggregationQuery();
        unset($query['type']);

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function getFlagsPlusNumbersQuery(array $selectedFlags): array
    {
        $query = parent::getFlagsPlusNumbersQuery($selectedFlags);
        unset($query['type']);

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function getBrandsPlusNumbersQuery(array $selectedBrandsIds): array
    {
        $query = parent::getBrandsPlusNumbersQuery($selectedBrandsIds);
        unset($query['type']);

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function getParametersPlusNumbersQuery(int $selectedParameterId, array $selectedValuesIds): array
    {
        $query = parent::getParametersPlusNumbersQuery($selectedParameterId, $selectedValuesIds);
        unset($query['type']);

        return $query;
    }
}

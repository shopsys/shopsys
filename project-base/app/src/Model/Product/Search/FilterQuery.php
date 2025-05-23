<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Override;
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
 */
class FilterQuery extends BaseFilterQuery
{
    protected const MAXIMUM_REASONABLE_AGGREGATION_BUCKET_COUNT = 400;

    /**
     * @var array<int, array<string, array<string, int>>>
     */
    private array $mustNot = [];

    /**
     * @param string $text
     * @return \App\Model\Product\Search\FilterQuery
     */
    #[Override]
    public function search(string $text): BaseFilterQuery
    {
        $clonedQuery = clone $this;

        $clonedQuery->match = [
            'multi_match' => [
                'query' => $text,
                'fields' => [
                    'searching_names.full_with_diacritic^60',
                    'searching_names.full_without_diacritic^50',
                    'searching_names^45',
                    'searching_names.edge_ngram_with_diacritic^40',
                    'searching_names.edge_ngram_without_diacritic^35',
                    'searching_catnums^50',
                    'searching_catnums.edge_ngram_unanalyzed_words^25',
                    'searching_partnos^40',
                    'searching_partnos.edge_ngram_unanalyzed_words^20',
                    'searching_eans^60',
                    'searching_eans.edge_ngram_unanalyzed_words^30',
                    'searching_short_descriptions^5',
                    'searching_descriptions^5',
                ],
            ],
        ];

        $clonedQuery->match['multi_match']['operator'] = 'and';

        return $clonedQuery;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getQuery(): array
    {
        $query = parent::getQuery();

        if (count($this->mustNot) > 0) {
            $query['body']['query']['bool']['must_not'] = $this->mustNot;
        }

        return $query;
    }
}

<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery as BaseFilterQuery;
use stdClass;

/**
 * @method \App\Model\Product\Search\FilterQuery applyOrdering(string $orderingModeId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \App\Model\Product\Search\FilterQuery applyDefaultOrdering()
 * @method \App\Model\Product\Search\FilterQuery filterByParameters(array $parameters)
 * @method \App\Model\Product\Search\FilterQuery filterByCategory(int[] $categoryIds)
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
    public function search(string $text): BaseFilterQuery
    {
        /** @var \App\Model\Product\Search\FilterQuery $clonedQuery */
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

//        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix.full_with_diacritic^60';
//        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix.full_without_diacritic^50';
//        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix^45';
//        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix.edge_ngram_with_diacritic^40';
//        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix.edge_ngram_without_diacritic^35';
//
//        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix.full_with_diacritic^60';
//        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix.full_without_diacritic^50';
//        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix^45';
//        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix.edge_ngram_with_diacritic^40';
//        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix.edge_ngram_without_diacritic^35';

        $clonedQuery->match['multi_match']['operator'] = 'and';

        return $clonedQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): array
    {
        $query = parent::getQuery();

        if (count($this->mustNot) > 0) {
            $query['body']['query']['bool']['must_not'] = $this->mustNot;
        }

        return $query;
    }

    /**
     * @return \App\Model\Product\Search\FilterQuery
     */
    public function orderByStockQuantity(): self
    {
        $clone = clone $this;

        $clone->sorting = [
            'stock_quantity' => 'desc',
            'id' => 'asc',
        ];

        return $clone;
    }

    /**
     * @param int $productId
     * @return \App\Model\Product\Search\FilterQuery
     */
    public function excludeProductByProductId(int $productId): self
    {
        $clone = clone $this;
        $clone->mustNot[] = [
            'term' => [
                'id' => $productId,
            ],
        ];

        return $clone;
    }

    /**
     * @return \App\Model\Product\Search\FilterQuery
     */
    public function filterNotExcludeOrInStock(): self
    {
        $clone = clone $this;

        $clone->filters[] = [
            'bool' => [
                'should' => [
                    [
                        'term' => [
                            'is_sale_exclusion' => false,
                        ],
                    ],
                    [
                        'term' => [
                            'in_stock' => true,
                        ],
                    ],
                ],
            ],
        ];

        return $clone;
    }

    /**
     * @return \App\Model\Product\Search\FilterQuery
     */
    public function filterOnlySellable(): self
    {
        $clone = clone $this;

        $clone->filters[] = [
            'term' => [
                'calculated_selling_denied' => false,
            ],
        ];

        // exclusion on current domain
        $clone->filters[] = [
            'term' => [
                'is_sale_exclusion' => false,
            ],
        ];

        return $clone;
    }

    /**
     * Answers question "If I add this parameter value, how many products will be added?"
     * We are looking for count of products that meet all filters and don't have already selected parameter value
     *
     * This query makes sense only within a single parameter, so it have to be executed for all parameters
     * (that have selected value and can have plus numbers)
     *
     * @see https://github.com/shopsys/shopsys/pull/1794
     * @param int $selectedParameterId
     * @param array $selectedValuesIds
     * @return array
     */
    public function getParametersPlusNumbersQuery(int $selectedParameterId, array $selectedValuesIds): array
    {
        return [
            'index' => $this->indexName,
            'body' => [
                'size' => 0,
                'aggs' => [
                    'parameters' => [
                        'nested' => [
                            'path' => 'parameters',
                        ],
                        'aggs' => [
                            'filtered_for_parameter' => [
                                'filter' => [
                                    'term' => [
                                        'parameters.parameter_id' => $selectedParameterId,
                                    ],
                                ],
                                'aggs' => [
                                    'by_parameters' => [
                                        'terms' => [
                                            'field' => 'parameters.parameter_id',
                                            'size' => static::MAXIMUM_REASONABLE_AGGREGATION_BUCKET_COUNT,
                                        ],
                                        'aggs' => [
                                            'by_value' => [
                                                'terms' => [
                                                    'field' => 'parameters.parameter_value_id',
                                                    'size' => static::MAXIMUM_REASONABLE_AGGREGATION_BUCKET_COUNT,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'query' => [
                    'bool' => [
                        'filter' => $this->filters,
                        'must' => [
                            [
                                'nested' => [
                                    'path' => 'parameters',
                                    'query' => [
                                        'bool' => [
                                            'must_not' => [
                                                'terms' => [
                                                    'parameters.parameter_value_id' => $selectedValuesIds,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $minimalPrice
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $maximalPrice
     * @return \App\Model\Product\Search\FilterQuery
     */
    public function filterByPrices(
        PricingGroup $pricingGroup,
        ?Money $minimalPrice = null,
        ?Money $maximalPrice = null,
    ): self {
        $clone = clone $this;
        $priceGte = null;
        $priceLte = null;

        if ($minimalPrice !== null) {
            $priceGte = (float)$minimalPrice->getAmount();
        }

        if ($maximalPrice !== null) {
            $priceLte = (float)$maximalPrice->getAmount();
        }

        $clone->filters[] = [
            'nested' => [
                'path' => 'prices',
                'query' => [
                    'bool' => [
                        'must' => [
                            'match_all' => new stdClass(),
                        ],
                        'filter' => [
                            'bool' => [
                                'must' => [
                                    [
                                        'range' => [
                                            'prices.filtering_minimal_price' => [
                                                'gte' => $priceGte,
                                            ],
                                        ],
                                    ],
                                    [
                                        'range' => [
                                            'prices.filtering_maximal_price' => [
                                                'lte' => $priceLte,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $clone;
    }

    /**
     * Applies all filters for filter
     * For flags, brands, stock, parameters, min and max price
     * Parameters aggregation have nested structure in result [parameter_id][parameter_value_id]
     *
     * @param int $pricingGroupId
     * @return array
     */
    public function getAggregationQueryForProductFilterConfig(int $pricingGroupId): array
    {
        $query = $this->getAbsoluteNumbersWithParametersQuery();

        $query['body']['aggs']['prices'] = [
            'nested' => [
                'path' => 'prices',
            ],
            'aggs' => [
                'filter_pricing_group' => [
                    'filter' => [
                        'term' => [
                            'prices.pricing_group_id' => $pricingGroupId,
                        ],
                    ],
                    'aggs' => [
                        'min_price' => [
                            'min' => [
                                'field' => 'prices.price_with_vat',
                            ],
                        ],
                        'max_price' => [
                            'max' => [
                                'field' => 'prices.price_with_vat',
                            ],
                        ],
                    ],
                ],
            ],

        ];

        return $query;
    }

    /**
     * Applies all filters for filter
     * For flags, brands, stock, min and max price
     *
     * @param int $pricingGroupId
     * @return array
     */
    public function getAggregationQueryForProductFilterConfigWithoutParameters(int $pricingGroupId): array
    {
        $query = $this->getAggregationQueryForProductFilterConfig($pricingGroupId);

        // Remove parameters from filter
        unset($query['body']['aggs']['parameters']);

        return $query;
    }

    /**
     * @return array
     */
    public function getAggregationQueryForProductCountInCategories(): array
    {
        $query = $this->getQuery();
        $query['body']['aggs'] = [
            'by_categories' => [
                'terms' => ['field' => 'categories'],
            ],
        ];

        return $query;
    }

    /**
     * @param \App\Model\Product\Filter\ParameterFilterData[] $sliderParametersData
     * @return \App\Model\Product\Search\FilterQuery
     */
    public function filterBySliderParameters(array $sliderParametersData): self
    {
        $clone = clone $this;

        foreach ($sliderParametersData as $sliderParameterData) {
            $parameterRange = [
                'gte' => $sliderParameterData->minimalValue,
                'lte' => $sliderParameterData->maximalValue,
            ];

            $clone->filters[] = [
                'nested' => [
                    'path' => 'parameters',
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match_all' => new stdClass(),
                            ],
                            'filter' => [
                                [
                                    'term' => [
                                        'parameters.parameter_id' => $sliderParameterData->parameter->getId(),
                                    ],
                                ],
                                [
                                    'range' => [
                                        'parameters.parameter_value_for_slider_filter' => $parameterRange,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        return $clone;
    }
}

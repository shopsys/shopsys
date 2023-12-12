<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use stdClass;

class FilterQuery
{
    protected const MAXIMUM_REASONABLE_AGGREGATION_BUCKET_COUNT = 100;

    /**
     * @var array<string, mixed>
     */
    protected array $filters = [];

    /**
     * @var array<string, mixed>
     */
    protected array $sorting = [];

    protected int $limit = 1000;

    protected int $page = 1;

    /**
     * @var array<string, mixed>
     */
    protected array $match;

    protected ?int $from = null;

    /**
     * @param string $indexName
     */
    public function __construct(protected readonly string $indexName)
    {
        $this->match = $this->matchAll();
    }

    /**
     * Default Elasticsearch ordering is by relevance, represented by _score field
     * In case you need to alter the ordering by relevance behavior, you can add condition
     * if ($orderingModeId === ProductListOrderingConfig::ORDER_BY_RELEVANCE)
     *
     * @param string $orderingModeId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function applyOrdering(string $orderingModeId, PricingGroup $pricingGroup): self
    {
        $clone = clone $this;

        $clone->sorting = [];

        if ($orderingModeId === ProductListOrderingConfig::ORDER_BY_RELEVANCE) {
            $clone->sorting['_score'] = 'desc';

            return $clone;
        }

        $clone->sorting['availability_dispatch_time'] = [
            'order' => 'asc',
            'missing' => '_last',
        ];

        if ($orderingModeId === ProductListOrderingConfig::ORDER_BY_PRIORITY) {
            $clone->sorting['ordering_priority'] = 'desc';
            $clone->sorting['name.keyword'] = 'asc';

            return $clone;
        }

        if ($orderingModeId === ProductListOrderingConfig::ORDER_BY_NAME_ASC) {
            $clone->sorting['name.keyword'] = 'asc';

            return $clone;
        }

        if ($orderingModeId === ProductListOrderingConfig::ORDER_BY_NAME_DESC) {
            $clone->sorting['name.keyword'] = 'desc';

            return $clone;
        }

        if ($orderingModeId === ProductListOrderingConfig::ORDER_BY_PRICE_ASC) {
            $clone->sorting['prices.price_with_vat'] = [
                'order' => 'asc',
                'nested' => [
                    'path' => 'prices',
                    'filter' => [
                        'term' => [
                            'prices.pricing_group_id' => $pricingGroup->getId(),
                        ],
                    ],
                ],
            ];
            $clone->sorting['ordering_priority'] = 'desc';
            $clone->sorting['name.keyword'] = 'asc';

            return $clone;
        }

        if ($orderingModeId === ProductListOrderingConfig::ORDER_BY_PRICE_DESC) {
            $clone->sorting['prices.price_with_vat'] = [
                'order' => 'desc',
                'nested' => [
                    'path' => 'prices',
                    'filter' => [
                        'term' => [
                            'prices.pricing_group_id' => $pricingGroup->getId(),
                        ],
                    ],
                ],
            ];
            $clone->sorting['ordering_priority'] = 'desc';
            $clone->sorting['name.keyword'] = 'asc';

            return $clone;
        }

        return $clone;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function applyDefaultOrdering(): self
    {
        $clone = clone $this;

        $clone->sorting = [
            'ordering_priority' => 'desc',
            'name.keyword' => 'asc',
        ];

        return $clone;
    }

    /**
     * @param int[] $ids
     * @return $this
     */
    public function applyOrderingByIdsArray(array $ids): self
    {
        $clone = clone $this;

        $orderIndexedByIds = [];
        $order = 0;

        foreach ($ids as $id) {
            $orderIndexedByIds[$id] = $order;
            $order++;
        }

        $clone->sorting = [
            '_script' => [
                'type' => 'number',
                'script' => [
                    'lang' => 'painless',
                    'source' => 'def a=doc[\'id\'].value; return params.sort[a.toString()];',
                    'params' => [
                        'sort' => $orderIndexedByIds,
                    ],
                ],
                'order' => 'asc',
            ],
        ];

        return $clone;
    }

    /**
     * @param array $parameters
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterByParameters(array $parameters): self
    {
        $clone = clone $this;

        foreach ($parameters as $parameterId => $parameterValues) {
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
                                        'parameters.parameter_id' => $parameterId,
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'parameters.parameter_value_id' => $parameterValues,
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $minimalPrice
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $maximalPrice
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterByPrices(
        PricingGroup $pricingGroup,
        ?Money $minimalPrice = null,
        ?Money $maximalPrice = null,
    ): self {
        $clone = clone $this;

        $prices = [];

        if ($minimalPrice !== null) {
            $prices['gte'] = (float)$minimalPrice->getAmount();
        }

        if ($maximalPrice !== null) {
            $prices['lte'] = (float)$maximalPrice->getAmount();
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
                            [
                                'term' => [
                                    'prices.pricing_group_id' => $pricingGroup->getId(),
                                ],
                            ],
                            [
                                'range' => [
                                    'prices.price_with_vat' => $prices,
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
     * @param int[] $categoryIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterByCategory(array $categoryIds): self
    {
        $clone = clone $this;

        $clone->filters[] = [
            'terms' => [
                'categories' => $categoryIds,
            ],
        ];

        return $clone;
    }

    /**
     * @param int[] $brandIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterByBrands(array $brandIds): self
    {
        $clone = clone $this;

        $clone->filters[] = [
            'terms' => [
                'brand' => $brandIds,
            ],
        ];

        return $clone;
    }

    /**
     * @param int[] $flagIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterByFlags(array $flagIds): self
    {
        $clone = clone $this;

        $clone->filters[] = [
            'terms' => [
                'flags' => $flagIds,
            ],
        ];

        return $clone;
    }

    /**
     * @param int[] $productIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterByProductIds(array $productIds): self
    {
        $clone = clone $this;

        $clone->filters[] = [
            'terms' => [
                'id' => $productIds,
            ],
        ];

        return $clone;
    }

    /**
     * @param string[] $productUuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterByProductUuids(array $productUuids): self
    {
        $clone = clone $this;

        $clone->filters[] = [
            'terms' => [
                'uuid' => $productUuids,
            ],
        ];

        return $clone;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterOutVariants(): self
    {
        $clone = clone $this;

        $clone->filters[] = [
            'term' => [
                'is_variant' => false,
            ],
        ];

        return $clone;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterOnlyInStock(): self
    {
        $clone = clone $this;

        $clone->filters[] = [
            'term' => [
                'in_stock' => true,
            ],
        ];

        return $clone;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterOnlySellable(): self
    {
        $clone = clone $this;

        $clone->filters[] = [
            'term' => [
                'calculated_selling_denied' => false,
            ],
        ];

        return $clone;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterOnlyVisible(PricingGroup $pricingGroup): self
    {
        $clone = clone $this;

        $clone->filters[] = [
            'nested' => [
                'path' => 'visibility',
                'query' => [
                    'bool' => [
                        'must' => [
                            'match_all' => new stdClass(),
                        ],
                        'filter' => [
                            [
                                'term' => [
                                    'visibility.pricing_group_id' => $pricingGroup->getId(),
                                ],
                            ],
                            [
                                'term' => [
                                    'visibility.visible' => true,
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
     * @param string $text
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function search(string $text): self
    {
        $clone = clone $this;

        $clone->match = [
            'multi_match' => [
                'query' => $text,
                'fields' => [
                    'name.full_with_diacritic^60',
                    'name.full_without_diacritic^50',
                    'name^45',
                    'name.edge_ngram_with_diacritic^40',
                    'name.edge_ngram_without_diacritic^35',
                    'catnum^50',
                    'catnum.edge_ngram^25',
                    'partno^40',
                    'partno.edge_ngram^20',
                    'ean^60',
                    'ean.edge_ngram^30',
                    'short_description^5',
                    'description^5',
                ],
            ],
        ];

        return $clone;
    }

    /**
     * @param int $page
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function setPage(int $page): self
    {
        $clone = clone $this;

        $clone->page = $page;

        return $clone;
    }

    /**
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function setLimit(int $limit): self
    {
        $clone = clone $this;

        $clone->limit = $limit;

        return $clone;
    }

    /**
     * @param int $from
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function setFrom(int $from): self
    {
        $clone = clone $this;

        $clone->from = $from;

        return $clone;
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return [
            'index' => $this->indexName,
            'body' => [
                'from' => $this->from !== null ? $this->from : $this->countFrom($this->page, $this->limit),
                'size' => $this->limit,
                'sort' => $this->sorting,
                'query' => [
                    'bool' => [
                        'must' => $this->match,
                        'filter' => $this->filters,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function matchAll(): array
    {
        return [
            'match_all' => new stdClass(),
        ];
    }

    /**
     * @param int $page
     * @param int $limit
     * @return int
     */
    protected function countFrom(int $page, int $limit): int
    {
        return ($page - 1) * $limit;
    }

    /**
     * Applies all filters and calculate standard (non pluses) numbers
     * For flags, brands and stock
     *
     * @return array
     */
    public function getAbsoluteNumbersAggregationQuery(): array
    {
        return [
            'index' => $this->indexName,
            'body' => [
                'size' => 0,
                'aggs' => [
                    'flags' => [
                        'terms' => [
                            'field' => 'flags',
                            'size' => static::MAXIMUM_REASONABLE_AGGREGATION_BUCKET_COUNT,
                        ],
                    ],
                    'brands' => [
                        'terms' => [
                            'field' => 'brand',
                            'size' => static::MAXIMUM_REASONABLE_AGGREGATION_BUCKET_COUNT,
                        ],
                    ],
                    'stock' => [
                        'filter' => [
                            'term' => [
                                'in_stock' => 'true',
                            ],
                        ],
                    ],
                ],
                'query' => [
                    'bool' => [
                        'must' => $this->match,
                        'filter' => $this->filters,
                    ],
                ],
            ],
        ];
    }

    /**
     * Applies all filters and calculate standard (non pluses) numbers
     * For flags, brands, stock, parameters
     * Parameters aggregation have nested structure in result [parameter_id][parameter_value_id]
     *
     * @return array
     */
    public function getAbsoluteNumbersWithParametersQuery(): array
    {
        $query = $this->getAbsoluteNumbersAggregationQuery();
        $query['body']['aggs']['parameters'] = [
            'nested' => [
                'path' => 'parameters',
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
        ];

        return $query;
    }

    /**
     * Answers question "If I add this flag, how many products will be added?"
     * We are looking for count of products that meet all filters and don't have any of already selected flags
     *
     * @param int[] $selectedFlags
     * @return array
     */
    public function getFlagsPlusNumbersQuery(array $selectedFlags): array
    {
        return [
            'index' => $this->indexName,
            'body' => [
                'size' => 0,
                'aggs' => [
                    'flags' => [
                        'terms' => [
                            'field' => 'flags',
                            'size' => static::MAXIMUM_REASONABLE_AGGREGATION_BUCKET_COUNT,
                        ],
                    ],
                ],
                'query' => [
                    'bool' => [
                        'must' => $this->match,
                        'filter' => $this->filters,
                        'must_not' => [
                            'terms' => [
                                'flags' => $selectedFlags,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Answers question "If I add this brand, how many products will be added?"
     * We are looking for count of products that meet all filters and don't have any of already selected brand
     *
     * @param int[] $selectedBrandsIds
     * @return array
     */
    public function getBrandsPlusNumbersQuery(array $selectedBrandsIds): array
    {
        return [
            'index' => $this->indexName,
            'body' => [
                'size' => 0,
                'aggs' => [
                    'brands' => [
                        'terms' => [
                            'field' => 'brand',
                            'size' => static::MAXIMUM_REASONABLE_AGGREGATION_BUCKET_COUNT,
                        ],
                    ],
                ],
                'query' => [
                    'bool' => [
                        'must' => $this->match,
                        'filter' => $this->filters,
                        'must_not' => [
                            'terms' => [
                                'brand' => $selectedBrandsIds,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Answers question "If I add this parameter value, how many products will be added?"
     * We are looking for count of products that meet all filters and don't have already selected parameter value
     *
     * This query makes sense only within a single parameter, so it have to be executed for all parameters
     * (that have selected value and can have plus numbers)
     *
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
}

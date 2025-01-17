<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
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
     * @var array<string, mixed>
     */
    protected array $runtimeFields = [];

    /**
     * @var string[]
     */
    protected array $fields = [];

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

        if ($orderingModeId === ProductListOrderingConfig::ORDER_BY_PRIORITY) {
            $clone->sorting['priority_by_product_type'] = 'desc';
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
            $clone->runtimeFields += $this->getMinCurrentSellingPriceRuntimeField($pricingGroup->getId());

            $clone->sorting['_script'] = $this->getInquirySorting();
            $clone->sorting['min_current_selling_price_with_vat'] = 'asc';
            $clone->sorting['ordering_priority'] = 'desc';
            $clone->sorting['name.keyword'] = 'asc';

            return $clone;
        }

        if ($orderingModeId === ProductListOrderingConfig::ORDER_BY_PRICE_DESC) {
            $clone->runtimeFields += $this->getMinCurrentSellingPriceRuntimeField($pricingGroup->getId());

            $clone->sorting['_script'] = $this->getInquirySorting();
            $clone->sorting['min_current_selling_price_with_vat'] = 'desc';
            $clone->sorting['ordering_priority'] = 'desc';
            $clone->sorting['name.keyword'] = 'asc';

            return $clone;
        }

        return $clone;
    }

    /**
     * @param int $pricingGroupId
     * @return array
     */
    protected function getMinCurrentSellingPriceRuntimeField(int $pricingGroupId): array
    {
        $scriptMinValue = "
            double finalPrice = Double.MAX_VALUE;
            DateTimeFormatter formatter = java.time.format.DateTimeFormatter.ofPattern('yyyy-MM-dd HH:mm:ss').withZone(java.time.ZoneOffset.UTC);

            if (!params['_source']['prices'].isEmpty()) {
                for (def price : params['_source']['prices']) {
                    if (price['pricing_group_id'] === params['pricing_group_id']) {
                        finalPrice = Math.min(finalPrice, price['filtering_maximal_price']);
                        break;
                    }
                }
            }

            if (!params['_source']['special_prices'].isEmpty()) {
                def currentDate = java.time.ZonedDateTime.parse(params['current_date'], formatter).toInstant();

                Set usedProductIds = new HashSet();

                for (def specialPrice : params['_source']['special_prices']) {
                    def validFrom = java.time.ZonedDateTime.parse(specialPrice['valid_from'], formatter).toInstant();
                    def validTo = java.time.ZonedDateTime.parse(specialPrice['valid_to'], formatter).toInstant();

                    if ((validFrom.isBefore(currentDate) || validFrom.equals(currentDate)) && (validTo.isAfter(currentDate) || validTo.equals(currentDate))) {

                        for (def price : specialPrice['prices']) {
                            if (usedProductIds.contains(price['product_id'])) {
                                continue;
                            }

                            finalPrice = Math.min(finalPrice, price['price_with_vat']);
                            usedProductIds.add(price['product_id'])
                        }
                    }
                }
            }

            emit(finalPrice);";

        return [
            'min_current_selling_price_with_vat' => [
                'type' => 'double',
                'script' => [
                    'source' => $scriptMinValue,
                    'params' => [
                        'pricing_group_id' => $pricingGroupId,
                        'current_date' => date('Y-m-d H:i:s'),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $pricingGroupId
     * @return array[]
     */
    public function getMaxCurrentSellingPriceRuntimeField(int $pricingGroupId): array
    {
        $scriptMaxValue = "
            double finalPrice = 0;
            DateTimeFormatter formatter = java.time.format.DateTimeFormatter.ofPattern('yyyy-MM-dd HH:mm:ss').withZone(java.time.ZoneOffset.UTC);

            if (!params['_source']['prices'].isEmpty()) {
                for (def price : params['_source']['prices']) {
                    if (price['pricing_group_id'] === params['pricing_group_id']) {
                        finalPrice = Math.max(finalPrice, price['filtering_minimal_price']);
                        break;
                    }
                }
            }

            if (!params['_source']['special_prices'].isEmpty()) {
                def currentDate = java.time.ZonedDateTime.parse(params['current_date'], formatter).toInstant();

                Set usedProductIds = new HashSet();

                for (def specialPrice : params['_source']['special_prices']) {
                    def validFrom = java.time.ZonedDateTime.parse(specialPrice['valid_from'], formatter).toInstant();
                    def validTo = java.time.ZonedDateTime.parse(specialPrice['valid_to'], formatter).toInstant();

                    if ((validFrom.isBefore(currentDate) || validFrom.equals(currentDate)) && (validTo.isAfter(currentDate) || validTo.equals(currentDate))) {

                        for (def price : specialPrice['prices']) {
                            if (usedProductIds.contains(price['product_id'])) {
                                continue;
                            }

                            finalPrice = Math.max(finalPrice, price['price_with_vat']);
                            usedProductIds.add(price['product_id'])
                        }
                    }
                }
            }

            emit(finalPrice);
        ";

        return [
            'max_current_selling_price_with_vat' => [
                'type' => 'double',
                'script' => [
                    'source' => $scriptMaxValue,
                    'params' => [
                        'pricing_group_id' => $pricingGroupId,
                        'current_date' => date('Y-m-d H:i:s'),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getInquirySorting(): array
    {
        return [
            'type' => 'number',
            'script' => [
                'lang' => 'painless',
                'source' => 'doc[\'product_type\'].value == \'inquiry\' ? 1 : 0',
            ],
            'order' => 'asc',
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function applyOrderingByIdAscending(): self
    {
        $clone = clone $this;

        $clone->sorting = [
            'id' => 'asc',
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
        $priceGte = null;
        $priceLte = null;

        if ($minimalPrice !== null) {
            $priceGte = (float)$minimalPrice->getAmount();
        }

        if ($maximalPrice !== null) {
            $priceLte = (float)$maximalPrice->getAmount();
        }

        $clone->runtimeFields += $this->getMinCurrentSellingPriceRuntimeField($pricingGroup->getId());
        $clone->runtimeFields += $this->getMaxCurrentSellingPriceRuntimeField($pricingGroup->getId());

        $clone->filters[] = [
            'bool' => [
                'should' => [
                    [
                        'bool' => [
                            'must' => [
                                'match_all' => new stdClass(),
                            ],
                            'filter' => [
                                [
                                    'range' => [
                                        'max_current_selling_price_with_vat' => [
                                            'gte' => $priceGte,
                                        ],
                                    ],
                                ],
                                [
                                    'range' => [
                                        'min_current_selling_price_with_vat' => [
                                            'lte' => $priceLte,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'term' => [
                            'product_type' => ProductTypeEnum::TYPE_INQUIRY,
                        ],
                    ],
                ],
                'minimum_should_match' => 1,
            ],
        ];

        return $clone;
    }

    /**
     * @param int $categoryId
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterByCategory(int $categoryId): self
    {
        $clone = clone $this;

        $clone->filters[] = [
            'term' => [
                'categories' => $categoryId,
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
     * @param \DateTimeImmutable $sellingFrom
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function filterBySellingFrom(DateTimeImmutable $sellingFrom): self
    {
        $clone = clone $this;

        $clone->filters[] = [
            'bool' => [
                'must' => [
                    [
                        'exists' => [
                            'field' => 'selling_from',
                        ],
                    ], [
                        'range' => [
                            'selling_from' => [
                                'gte' => $sellingFrom->format('Y-m-d H:i:s'),
                                'lte' => 'now',
                            ],
                        ],
                    ],
                ],
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

        // exclusion on current domain
        $clone->filters[] = [
            'term' => [
                'is_sale_exclusion' => false,
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
        $query = [
            'index' => $this->indexName,
            'body' => [
                'from' => $this->from ?? $this->countFrom($this->page, $this->limit),
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

        if ($this->runtimeFields !== []) {
            $query['body']['runtime_mappings'] = $this->runtimeFields;
        }

        if ($this->fields !== []) {
            $query['body']['_source'] = false;
            $query['body']['fields'] = $this->fields;
        }

        return $query;
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
        $query = [
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

        if ($this->runtimeFields !== []) {
            $query['body']['runtime_mappings'] = $this->runtimeFields;
        }

        return $query;
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
        $query = [
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

        if ($this->runtimeFields !== []) {
            $query['body']['runtime_mappings'] = $this->runtimeFields;
        }

        return $query;
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
        $query = [
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

        if ($this->runtimeFields !== []) {
            $query['body']['runtime_mappings'] = $this->runtimeFields;
        }

        return $query;
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
        $query = [
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

        if ($this->runtimeFields !== []) {
            $query['body']['runtime_mappings'] = $this->runtimeFields;
        }

        return $query;
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

        $query['body']['runtime_mappings'] = $this->getMinCurrentSellingPriceRuntimeField($pricingGroupId);

        $query['body']['aggs']['prices'] = [
            'filter' => [
                'bool' => [
                    'must_not' => [
                        'term' => [
                            'product_type' => ProductTypeEnum::TYPE_INQUIRY,
                        ],
                    ],
                ],
            ],
            'aggs' => [
                'min_price' => [
                    'min' => [
                        'field' => 'min_current_selling_price_with_vat',
                    ],
                ],
                'max_price' => [
                    'max' => [
                        'field' => 'min_current_selling_price_with_vat',
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

    /**
     * @param string[] $fields
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function restrictFields(array $fields): self
    {
        $clone = clone $this;

        $clone->fields = $fields;

        return $clone;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData[] $sliderParametersData
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
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

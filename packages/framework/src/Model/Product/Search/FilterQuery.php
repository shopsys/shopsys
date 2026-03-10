<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Search\SearchSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use stdClass;

class FilterQuery
{
    protected const int MAXIMUM_REASONABLE_AGGREGATION_BUCKET_COUNT = 400;

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

    protected bool $isFilteringBySpecialPriceActive = false;

    public function __construct(
        protected readonly string $indexName,
        protected readonly int $sellingPriceType,
    ) {
        $this->match = $this->matchAll();
    }

    /**
     * Default Elasticsearch ordering is by relevance, represented by _score field
     * In case you need to alter the ordering by relevance behavior, you can add condition
     * if ($orderingModeId === ProductListOrderingConfig::ORDER_BY_RELEVANCE)
     */
    public function applyOrdering(string $orderingModeId, PricingGroup $pricingGroup): static
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
            $clone->sorting['min_current_selling_price'] = 'asc';
            $clone->sorting['ordering_priority'] = 'desc';
            $clone->sorting['name.keyword'] = 'asc';

            return $clone;
        }

        if ($orderingModeId === ProductListOrderingConfig::ORDER_BY_PRICE_DESC) {
            $clone->runtimeFields += $this->getMinCurrentSellingPriceRuntimeField($pricingGroup->getId());

            $clone->sorting['_script'] = $this->getInquirySorting();
            $clone->sorting['min_current_selling_price'] = 'desc';
            $clone->sorting['ordering_priority'] = 'desc';
            $clone->sorting['name.keyword'] = 'asc';

            return $clone;
        }

        return $clone;
    }

    /**
     * @return array[]
     */
    protected function getMinCurrentSellingPriceRuntimeField(int $pricingGroupId): array
    {
        $priceFieldName = $this->sellingPriceType === PricingSetting::PRICE_TYPE_WITH_VAT ? 'price_with_vat' : 'price_without_vat';

        $scriptMinValue = "
            double finalPrice = Double.MAX_VALUE;
            DateTimeFormatter formatter = java.time.format.DateTimeFormatter.ofPattern('yyyy-MM-dd HH:mm:ss').withZone(java.time.ZoneOffset.UTC);

            if (!params['_source']['prices'].isEmpty()) {
                for (def price : params['_source']['prices']) {
                    if (price['pricing_group_id'] === params['pricing_group_id']) {
                        finalPrice = Math.min(finalPrice, price['" . $priceFieldName . "']);
                        for (def variantPrice : price['variant_prices']) {
                            if (variantPrice['" . $priceFieldName . "'] < finalPrice) {
                                finalPrice = variantPrice['" . $priceFieldName . "'];
                            }
                        }
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

                            finalPrice = Math.min(finalPrice, price['" . $priceFieldName . "']);
                            usedProductIds.add(price['product_id']);
                        }
                    }
                }
            }

            emit(finalPrice);";

        return [
            'min_current_selling_price' => [
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
     * @return array[]
     */
    public function getMaxCurrentSellingPriceRuntimeField(int $pricingGroupId): array
    {
        $priceFieldName = $this->sellingPriceType === PricingSetting::PRICE_TYPE_WITH_VAT ? 'price_with_vat' : 'price_without_vat';

        $scriptMaxValue = "
            double finalPrice = 0;
            DateTimeFormatter formatter = java.time.format.DateTimeFormatter.ofPattern('yyyy-MM-dd HH:mm:ss').withZone(java.time.ZoneOffset.UTC);
            int productId = params['_source']['id'];

            if (!params['_source']['prices'].isEmpty()) {
                for (def price : params['_source']['prices']) {
                    if (price['pricing_group_id'] === params['pricing_group_id']) {
                        finalPrice = Math.max(finalPrice, price['" . $priceFieldName . "']);
                        for (def variantPrice : price['variant_prices']) {
                            if (variantPrice['" . $priceFieldName . "'] > finalPrice) {
                                finalPrice = variantPrice['" . $priceFieldName . "'];
                                productId = variantPrice['variant_id'];                            
                            }
                        }
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

                            if (productId != price['product_id']) {
                                continue;
                            }

                            finalPrice = Math.min(finalPrice, price['" . $priceFieldName . "']);
                            usedProductIds.add(price['product_id']);
                        }
                    }
                }
            }

            emit(finalPrice);
        ";

        return [
            'max_current_selling_price' => [
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
     * @return array[]
     */
    protected function getHasActiveSpecialPriceRuntimeField(int $pricingGroupId): array
    {
        $priceFieldName = $this->sellingPriceType === PricingSetting::PRICE_TYPE_WITH_VAT ? 'price_with_vat' : 'price_without_vat';

        $script = "
            boolean hasActiveSpecialPrice = false;
            DateTimeFormatter formatter = java.time.format.DateTimeFormatter.ofPattern('yyyy-MM-dd HH:mm:ss').withZone(java.time.ZoneOffset.UTC);
            def currentDate = java.time.ZonedDateTime.parse(params['current_date'], formatter).toInstant();

            for (def price : params['_source']['prices']) {
                if (price['pricing_group_id'] === params['pricing_group_id']) {
                    if (params['_source']['is_main_variant'] === false) {
                        double basicPrice = price['" . $priceFieldName . "'];

                        for (def specialPrice : params['_source']['special_prices']) {
                            def validFrom = java.time.ZonedDateTime.parse(specialPrice['valid_from'], formatter).toInstant();
                            def validTo = java.time.ZonedDateTime.parse(specialPrice['valid_to'], formatter).toInstant();
            
                            if ((validFrom.isBefore(currentDate) || validFrom.equals(currentDate)) &&
                                (validTo.isAfter(currentDate) || validTo.equals(currentDate))) {
                                for (def specialPriceData : specialPrice['prices']) {
                                    if (basicPrice > specialPriceData['" . $priceFieldName . "']) {
                                        hasActiveSpecialPrice = true;
                                        break;
                                    }
                                }
                            }
                            if (hasActiveSpecialPrice) break;
                        }
                    } else {
                        for (def variantPrice : price['variant_prices']) {
                            double variantBasicPrice = variantPrice['" . $priceFieldName . "'];
                            for (def specialPrice : params['_source']['special_prices']) {
                                def validFrom = java.time.ZonedDateTime.parse(specialPrice['valid_from'], formatter).toInstant();
                                def validTo = java.time.ZonedDateTime.parse(specialPrice['valid_to'], formatter).toInstant();
        
                                if ((validFrom.isBefore(currentDate) || validFrom.equals(currentDate)) &&
                                    (validTo.isAfter(currentDate) || validTo.equals(currentDate))) {
                                    for (def specialPriceData : specialPrice['prices']) {
                                        if (specialPriceData['product_id'] === variantPrice['variant_id'] &&
                                            specialPriceData['" . $priceFieldName . "'] < variantBasicPrice) {
                                            hasActiveSpecialPrice = true;
                                            break;
                                        }
                                    }
                                }
                            }
    
                            if (hasActiveSpecialPrice) break;
                        }
                        if (hasActiveSpecialPrice) break;
                    }
                    break;
                }
            }
    
            emit(hasActiveSpecialPrice);
        ";

        return [
            'has_active_special_price' => [
                'type' => 'boolean',
                'script' => [
                    'source' => $script,
                    'params' => [
                        'pricing_group_id' => $pricingGroupId,
                        'current_date' => date('Y-m-d H:i:s'),
                    ],
                ],
            ],
        ];
    }

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

    public function applyOrderingByIdAscending(): static
    {
        $clone = clone $this;

        $clone->sorting = [
            'id' => 'asc',
        ];

        return $clone;
    }

    /**
     * @param int[] $ids
     */
    public function applyOrderingByIdsArray(array $ids): static
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

    public function filterByParameters(array $parameters): static
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

    public function filterByPrices(
        PricingGroup $pricingGroup,
        ?Money $minimalPrice = null,
        ?Money $maximalPrice = null,
    ): static {
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
                                        'max_current_selling_price' => [
                                            'gte' => $priceGte,
                                        ],
                                    ],
                                ],
                                [
                                    'range' => [
                                        'min_current_selling_price' => [
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

    public function filterByCategory(int $categoryId): static
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
     */
    public function filterByBrands(array $brandIds): static
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
     */
    public function filterByFlags(array $flagIds): static
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
     */
    public function filterByProductIds(array $productIds): static
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
     */
    public function filterByProductUuids(array $productUuids): static
    {
        $clone = clone $this;

        $clone->filters[] = [
            'terms' => [
                'uuid' => $productUuids,
            ],
        ];

        return $clone;
    }

    public function filterOutVariants(): static
    {
        $clone = clone $this;

        $clone->filters[] = [
            'term' => [
                'is_variant' => false,
            ],
        ];

        return $clone;
    }

    public function filterOnlyInStock(): static
    {
        $clone = clone $this;

        $clone->filters[] = [
            'term' => [
                'in_stock' => true,
            ],
        ];

        return $clone;
    }

    public function filterOnlyInStockOrAllowedNegativeStock(): static
    {
        $clone = clone $this;

        $clone->filters[] = [
            'bool' => [
                'should' => [
                    [
                        'term' => [
                            'in_stock' => true,
                        ],
                    ],
                    [
                        'term' => [
                            'is_allowed_negative_stock' => true,
                        ],
                    ],
                ],
                'minimum_should_match' => 1,
            ],
        ];

        return $clone;
    }

    public function filterBySellingFrom(DateTimeImmutable $sellingFrom): static
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

    public function filterOnlySellable(): static
    {
        $clone = clone $this;

        $clone->filters[] = [
            'term' => [
                'selling_denied' => false,
            ],
        ];

        return $clone;
    }

    public function filterOnlyVisible(PricingGroup $pricingGroup): static
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

    public function filterWithActiveSpecialPriceOnly(PricingGroup $pricingGroup): static
    {
        $clone = clone $this;
        $clone->isFilteringBySpecialPriceActive = true;
        $clone->runtimeFields += $this->getHasActiveSpecialPriceRuntimeField($pricingGroup->getId());
        $clone->filters[] = [
            'term' => [
                'has_active_special_price' => true,
            ],
        ];

        return $clone;
    }

    protected function simpleSearch(string $text): static
    {
        $clone = clone $this;

        $clone->match = [
            'match_phrase_prefix' => [
                'searching_names.full_without_diacritic' => [
                    'query' => $text,
                ],
            ],
        ];

        return $clone;
    }

    public function search(string $text): static
    {
        if (mb_strlen($text) < SearchSetting::SIMPLE_SEARCH_THRESHOLD) {
            return $this->simpleSearch($text);
        }

        $clone = clone $this;

        $clone->match = [
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
                    'searching_seo_h1s.full_with_diacritic^20',
                    'searching_seo_h1s.full_without_diacritic^18',
                    'searching_seo_h1s^16',
                    'searching_seo_h1s.edge_ngram_with_diacritic^14',
                    'searching_seo_h1s.edge_ngram_without_diacritic^12',
                    'searching_seo_titles.full_with_diacritic^15',
                    'searching_seo_titles.full_without_diacritic^13',
                    'searching_seo_titles^11',
                    'searching_seo_titles.edge_ngram_with_diacritic^9',
                    'searching_seo_titles.edge_ngram_without_diacritic^7',
                    'searching_seo_meta_descriptions^4',
                ],
            ],
        ];

        $clone->match['multi_match']['operator'] = 'and';

        return $clone;
    }

    public function setPage(int $page): static
    {
        $clone = clone $this;

        $clone->page = $page;

        return $clone;
    }

    public function setLimit(int $limit): static
    {
        $clone = clone $this;

        $clone->limit = $limit;

        return $clone;
    }

    public function setFrom(int $from): static
    {
        $clone = clone $this;

        $clone->from = $from;

        return $clone;
    }

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

    protected function matchAll(): array
    {
        return [
            'match_all' => new stdClass(),
        ];
    }

    protected function countFrom(int $page, int $limit): int
    {
        return ($page - 1) * $limit;
    }

    /**
     * Applies all filters and calculate standard (non pluses) numbers
     * For flags, brands and stock
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
     */
    public function getAggregationQueryForProductFilterConfig(int $pricingGroupId): array
    {
        $query = $this->getAbsoluteNumbersWithParametersQuery();

        $query['body']['runtime_mappings'] = $this->getMinCurrentSellingPriceRuntimeField($pricingGroupId);

        if ($this->isFilteringBySpecialPriceActive) {
            $query['body']['runtime_mappings'] += $this->getHasActiveSpecialPriceRuntimeField($pricingGroupId);
        }

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
                        'field' => 'min_current_selling_price',
                    ],
                ],
                'max_price' => [
                    'max' => [
                        'field' => 'min_current_selling_price',
                    ],
                ],
            ],
        ];

        return $query;
    }

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
     */
    public function restrictFields(array $fields): static
    {
        $clone = clone $this;

        $clone->fields = $fields;

        return $clone;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData[] $sliderParametersData
     */
    public function filterBySliderParameters(array $sliderParametersData): static
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

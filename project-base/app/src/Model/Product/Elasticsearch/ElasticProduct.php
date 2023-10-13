<?php

declare(strict_types=1);

namespace App\Model\Product\Elasticsearch;

class ElasticProduct
{
    #[ElasticField(type: 'text')]
    public string $searchingNames;


    #[ElasticField(
        type: 'text',
        fields: [
            new ElasticField(
                name: 'full_with_diacritic',
                type: 'text',
                analyzer: 'full_with_diacritic'
            ),
            new ElasticField(
                name: 'full_without_diacritic',
                type: 'text',
                analyzer: 'full_without_diacritic',
            ),
            new ElasticField(
                name: 'edge_ngram_with_diacritic',
                type: 'text',
                analyzer: 'edge_ngram_with_diacritic',
                searchAnalyzer: 'full_with_diacritic'
            ),
            new ElasticField(
                name: 'edge_ngram_without_diacritic',
                type: 'text',
                analyzer: 'edge_ngram_without_diacritic',
                searchAnalyzer: 'full_without_diacritic'
            ),
            new ElasticField(
                name: 'keyword',
                type: 'icu_collation_keyword',
                language: '%domain_locale%',
                index: false
            ),
        ]
    )]
    public string $name;

    #[ElasticField(type: 'text')]
    public string $namePrefix;

    #[ElasticField(type: 'text')]
    public string $nameSufix;

    #[ElasticField(
        type: 'nested',
        properties: [
            new ElasticField(name: 'pricing_group_id', type: 'integer'),
            new ElasticField(name: 'price_with_vat', type: 'float'),
            new ElasticField(name: 'price_without_vat', type: 'float'),
            new ElasticField(name: 'vat', type: 'float'),
            new ElasticField(name: 'price_from', type: 'boolean'),
            new ElasticField(name: 'filtering_minimal_price', type: 'float'),
            new ElasticField(name: 'filtering_maximal_price', type: 'float'),
        ]
    )]
    public array $prices;

    #[ElasticField(type: 'boolean')]
    public bool $inStock;

    #[ElasticField(type: 'boolean')]
    public bool $isAvailable;

    #[ElasticField(
        type: 'nested',
        properties: [
            new ElasticField(name: 'parameter_id', type: 'integer'),
            new ElasticField(name: 'parameter_uuid', type: 'keyword'),
            new ElasticField(name: 'parameter_name', type: 'text'),
            new ElasticField(name: 'parameter_unit', type: 'text'),
            new ElasticField(name: 'parameter_group', type: 'text'),
            new ElasticField(name: 'parameter_value_id', type: 'integer'),
            new ElasticField(name: 'parameter_value_uuid', type: 'keyword'),
            new ElasticField(name: 'parameter_value_text', type: 'text'),
            new ElasticField(name: 'parameter_is_dimensional', type: 'boolean'),
            new ElasticField(name: 'parameter_value_for_slider_filter', type: 'float'),
        ]
    )]
    public array $parameters;

    #[ElasticField(type: 'integer')]
    public int $orderingPriority;

    #[ElasticField(type: 'boolean')]
    public bool $calculatedSellingDenied;

    #[ElasticField(type: 'boolean')]
    public bool $sellingDenied;

    #[ElasticField(type: 'text')]
    public string $availability;

    #[ElasticField(type: 'text')]
    public string $availabilityStatus;

    #[ElasticField(type: 'integer')]
    public int $availabilityDispatchTime;

    #[ElasticField(type: 'boolean')]
    public bool $isVariant;

    #[ElasticField(type: 'boolean')]
    public bool $isMainVariant;

    #[ElasticField(type: 'text')]
    public string $detailUrl;

    #[ElasticField(
        type: 'nested',
        properties: [
            new ElasticField(name: 'pricing_group_id', type: 'integer'),
            new ElasticField(name: 'visible', type: 'boolean'),
        ]
    )]
    public array $visibility;

    #[ElasticField(type: 'keyword')]
    public string $uuid;

    #[ElasticField(type: 'text')]
    public string $unit;

    #[ElasticField(type: 'integer')]
    public int $stockQuantity;

    #[ElasticField(type: 'boolean')]
    public bool $hasPreorder;

    #[ElasticField(type: 'integer')]
    public int $variants;

    #[ElasticField(type: 'integer')]
    public int $mainVariantId;

    #[ElasticField(type: 'text')]
    public string $seoH1;

    #[ElasticField(type: 'text')]
    public string $seoTitle;

    #[ElasticField(type: 'text')]
    public string $seoMetaDescription;

    #[ElasticField(type: 'boolean')]
    public bool $isSaleExclusion;

    #[ElasticField(type: 'text')]
    public string $productAvailableStoresCountInformation;

    #[ElasticField(
        type: 'nested',
        properties: [
            new ElasticField(name: 'store_name', type: 'text'),
            new ElasticField(name: 'store_id', type: 'integer'),
            new ElasticField(name: 'availability_information', type: 'text'),
            new ElasticField(name: 'availability_status', type: 'text'),
        ]
    )]
    public array $storeAvailabilitiesInformation;

    #[ElasticField(
        type: 'nested',
        properties: [
            new ElasticField(name: 'anchor_text', type: 'text'),
            new ElasticField(name: 'url', type: 'text'),
        ]
    )]
    public array $files;

    #[ElasticField(type: 'text')]
    public string $usps;

    #[ElasticField(type: 'integer')]
    public int $mainCategoryId;

    #[ElasticField(type: 'text')]
    public string $mainCategoryPath;

    #[ElasticField(type: 'text')]
    public string $slug;

    #[ElasticField(type: 'integer')]
    public int $availableStoresCount;

    #[ElasticField(type: 'integer')]
    public int $relatedProducts;

    #[ElasticField(properties: [
        new ElasticField(name: 'name', type: 'text'),
        new ElasticField(name: 'slug', type: 'keyword'),
    ])]
    public array $breadcrumb;
}

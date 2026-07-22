<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Search;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter;
use Shopsys\FrameworkBundle\Model\ProductReview\Elasticsearch\ProductReviewDocumentMapper;

class ProductElasticsearchConverterTest extends TestCase
{
    public function testFillEmptyFields(): void
    {
        $product = [
            'id' => 1,
            'name' => '47" LG 47LA790V (FHD)',
            'catnum' => '5965879P',
            'partno' => '47LA790V',
            'ean' => '8845781245928',
            'description' => 'At first glance its <strong> beautiful design </strong>',
            'short_description' => '47 "LG 47LA790V Luxury TV from the South Korean company LG bears 47LA790S',
        ];

        $expected = [
            'id' => 1,
            'name' => '47" LG 47LA790V (FHD)',
            'catnum' => '5965879P',
            'partno' => '47LA790V',
            'ean' => '8845781245928',
            'description' => 'At first glance its <strong> beautiful design </strong>',
            'short_description' => '47 "LG 47LA790V Luxury TV from the South Korean company LG bears 47LA790S',
            'slug' => '',
            'categories' => [],
            'flags' => [],
            'parameters' => [],
            'prices' => [],
            'special_prices' => [],
            'visibility' => [],
            'accessories' => [],
            'related_products' => [],
            'additional_services' => [],
            'ordering_priority' => 0,
            'in_stock' => false,
            'is_main_variant' => false,
            'is_variant' => false,
            'main_variant_id' => null,
            'variants' => [],
            'selling_denied' => true,
            'personal_pickup_only' => false,
            'brand' => null,
            'brand_name' => '',
            'brand_slug' => '',
            'main_category_id' => null,
            'seo_h1' => null,
            'seo_title' => null,
            'seo_meta_description' => null,
            'hreflang_links' => [],
            'product_type' => 'basic',
            'priority_by_product_type' => 0,
            'name_prefix' => null,
            'name_suffix' => null,
            'store_availabilities_information' => [],
            'available_stores_count' => null,
            'stock_quantity' => null,
            'is_allowed_negative_stock' => true,
            'uuid' => '00000000-0000-0000-0000-000000000000',
            'unit' => '',
            'selling_from' => null,
            'expected_restocking_date' => null,
            'product_videos' => [],
            'vat_percent' => '0',
            'promotion' => [
                'buy_quantity' => null,
                'free_quantity' => null,
            ],
            'searching_seo_titles' => '',
            'searching_seo_h1s' => '',
            'searching_seo_meta_descriptions' => '',
            'reviews' => [],
            'review_summary' => [
                'average_rating' => null,
                'total_count' => 0,
                'rating_counts' => [
                    0 => ['rating' => 5, 'count' => 0],
                    1 => ['rating' => 4, 'count' => 0],
                    2 => ['rating' => 3, 'count' => 0],
                    3 => ['rating' => 2, 'count' => 0],
                    4 => ['rating' => 1, 'count' => 0],
                ],
            ],
            'is_promoted' => false,
            'top_product_position' => null,
        ];

        $converter = new ProductElasticsearchConverter(new ProductReviewDocumentMapper());
        $this->assertSame($expected, $converter->fillEmptyFields($product));
    }

    public function testFillEmptyParameterFields(): void
    {
        $product = [
            'id' => 1,
            'name' => '47" LG 47LA790V (FHD)',
            'catnum' => '5965879P',
            'partno' => '47LA790V',
            'ean' => '8845781245928',
            'description' => 'At first glance its <strong> beautiful design </strong>',
            'short_description' => '47 "LG 47LA790V Luxury TV from the South Korean company LG bears 47LA790S',
            'parameters' => [
                [
                    'parameter_id' => 1,
                    'parameter_value_id' => 1,
                ],
            ],
        ];

        $expected = [
            'id' => 1,
            'name' => '47" LG 47LA790V (FHD)',
            'catnum' => '5965879P',
            'partno' => '47LA790V',
            'ean' => '8845781245928',
            'description' => 'At first glance its <strong> beautiful design </strong>',
            'short_description' => '47 "LG 47LA790V Luxury TV from the South Korean company LG bears 47LA790S',
            'parameters' => [
                [
                    'parameter_id' => 1,
                    'parameter_value_id' => 1,
                    'parameter_uuid' => '',
                    'parameter_name' => '',
                    'parameter_value_uuid' => '',
                    'parameter_value_text' => '',
                    'parameter_group' => null,
                    'parameter_unit' => null,
                    'parameter_value_for_slider_filter' => null,
                    'parameter_type' => null,
                    'parameter_value_rgbHex' => null,
                    'parameter_value_icon_anchor_text' => null,
                    'parameter_value_icon_url' => null,
                ],
            ],
            'slug' => '',
            'categories' => [],
            'flags' => [],
            'prices' => [],
            'special_prices' => [],
            'visibility' => [],
            'accessories' => [],
            'related_products' => [],
            'additional_services' => [],
            'ordering_priority' => 0,
            'in_stock' => false,
            'is_main_variant' => false,
            'is_variant' => false,
            'main_variant_id' => null,
            'variants' => [],
            'selling_denied' => true,
            'personal_pickup_only' => false,
            'brand' => null,
            'brand_name' => '',
            'brand_slug' => '',
            'main_category_id' => null,
            'seo_h1' => null,
            'seo_title' => null,
            'seo_meta_description' => null,
            'hreflang_links' => [],
            'product_type' => 'basic',
            'priority_by_product_type' => 0,
            'name_prefix' => null,
            'name_suffix' => null,
            'store_availabilities_information' => [],
            'available_stores_count' => null,
            'stock_quantity' => null,
            'is_allowed_negative_stock' => true,
            'uuid' => '00000000-0000-0000-0000-000000000000',
            'unit' => '',
            'selling_from' => null,
            'expected_restocking_date' => null,
            'product_videos' => [],
            'vat_percent' => '0',
            'promotion' => [
                'buy_quantity' => null,
                'free_quantity' => null,
            ],
            'searching_seo_titles' => '',
            'searching_seo_h1s' => '',
            'searching_seo_meta_descriptions' => '',
            'reviews' => [],
            'review_summary' => [
                'average_rating' => null,
                'total_count' => 0,
                'rating_counts' => [
                    0 => ['rating' => 5, 'count' => 0],
                    1 => ['rating' => 4, 'count' => 0],
                    2 => ['rating' => 3, 'count' => 0],
                    3 => ['rating' => 2, 'count' => 0],
                    4 => ['rating' => 1, 'count' => 0],
                ],
            ],
            'is_promoted' => false,
            'top_product_position' => null,
        ];

        $converter = new ProductElasticsearchConverter(new ProductReviewDocumentMapper());
        $this->assertSame($expected, $converter->fillEmptyFields($product));
    }
}

<?php

namespace Tests\FrameworkBundle\Unit\Model\Product\Search;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter;

class ProductElasticsearchConverterTest extends TestCase
{
    public function testFillEmptyFields(): void
    {
        $product = [
            'name' => '47" LG 47LA790V (FHD)',
            'catnum' => '5965879P',
            'partno' => '47LA790V',
            'ean' => '8845781245928',
            'description' => 'At first glance its <strong> beautiful design </strong>',
            'short_description' => '47 "LG 47LA790V Luxury TV from the South Korean company LG bears 47LA790S',
        ];

        $expected = [
            'name' => '47" LG 47LA790V (FHD)',
            'catnum' => '5965879P',
            'partno' => '47LA790V',
            'ean' => '8845781245928',
            'description' => 'At first glance its <strong> beautiful design </strong>',
            'short_description' => '47 "LG 47LA790V Luxury TV from the South Korean company LG bears 47LA790S',
            'availability' => '',
            'detail_url' => '',
            'categories' => [],
            'flags' => [],
            'parameters' => [],
            'prices' => [],
            'visibility' => [],
            'ordering_priority' => 0,
            'in_stock' => false,
            'is_main_variant' => false,
            'main_variant_id' => null,
            'calculated_selling_denied' => true,
            'selling_denied' => true,
            'brand' => null,
            'brand_name' => '',
            'brand_url' => '',
            'main_category_id' => null,
            'seo_h1' => null,
            'seo_title' => null,
            'seo_meta_description' => null,
        ];

        $converter = new ProductElasticsearchConverter();
        $this->assertSame($expected, $converter->fillEmptyFields($product));
    }

    public function testFillEmptyParameterFields(): void
    {
        $product = [
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
            'name' => '47" LG 47LA790V (FHD)',
            'catnum' => '5965879P',
            'partno' => '47LA790V',
            'ean' => '8845781245928',
            'description' => 'At first glance its <strong> beautiful design </strong>',
            'short_description' => '47 "LG 47LA790V Luxury TV from the South Korean company LG bears 47LA790S',
            'parameters' => [
                [
                    'parameter_id' => 1,
                    'parameter_name' => '',
                    'parameter_value_id' => 1,
                    'parameter_value_text' => '',
                ],
            ],
            'availability' => '',
            'detail_url' => '',
            'categories' => [],
            'flags' => [],
            'prices' => [],
            'visibility' => [],
            'ordering_priority' => 0,
            'in_stock' => false,
            'is_main_variant' => false,
            'main_variant_id' => null,
            'calculated_selling_denied' => true,
            'selling_denied' => true,
            'brand' => null,
            'brand_name' => '',
            'brand_url' => '',
            'main_category_id' => null,
            'seo_h1' => null,
            'seo_title' => null,
            'seo_meta_description' => null,
        ];

        $converter = new ProductElasticsearchConverter();
        $this->assertSame($expected, $converter->fillEmptyFields($product));
    }
}

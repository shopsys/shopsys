<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductExportRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductExportRepository $repository;

    public function testProductDataHaveExpectedStructure(): void
    {
        $data = $this->repository->getProductsData($this->domain->getId(), $this->domain->getLocale(), 0, 10);
        $this->assertCount(10, $data);

        $structure = array_keys(array_first($data));
        sort($structure);

        $expectedStructure = $this->getExpectedStructureForRepository();

        sort($expectedStructure);

        $this->assertSame($expectedStructure, $structure);
    }

    /**
     * @return string[]
     */
    private function getExpectedStructureForRepository(): array
    {
        return [
            'id',
            'catnum',
            'partno',
            'ean',
            'name',
            'description',
            'short_description',
            'brand',
            'brand_name',
            'brand_slug',
            'flags',
            'categories',
            'main_category_id',
            'main_category_path',
            'in_stock',
            'prices',
            'special_prices',
            'parameters',
            'ordering_priority',
            'selling_denied',
            'availability',
            'availability_status',
            'is_main_variant',
            'is_variant',
            'slug',
            'visibility',
            'product_type',
            'priority_by_product_type',
            'uuid',
            'unit',
            'stock_quantity',
            'is_allowed_negative_stock',
            'variants',
            'main_variant_id',
            'seo_h1',
            'seo_title',
            'seo_meta_description',
            'accessories',
            'name_prefix',
            'name_suffix',
            'store_availabilities_information',
            'usps',
            ...$this->getSearchingFields(),
            'available_stores_count',
            'related_products',
            'breadcrumb',
            'product_videos',
            'hreflang_links',
            'selling_from',
            'vat_percent',
            'promotion',
            'is_promoted',
            'top_product_position',
            'zbozi_category',
        ];
    }

    /**
     * @return string[]
     */
    private function getSearchingFields(): array
    {
        return [
            'searching_names',
            'searching_descriptions',
            'searching_catnums',
            'searching_eans',
            'searching_partnos',
            'searching_short_descriptions',
            'searching_seo_titles',
            'searching_seo_h1s',
            'searching_seo_meta_descriptions',
        ];
    }
}

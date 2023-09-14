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

        $structure = array_keys(reset($data));
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
            'brand_url',
            'flags',
            'categories',
            'main_category_id',
            'main_category_path',
            'in_stock',
            'is_available',
            'prices',
            'parameters',
            'ordering_priority',
            'breadcrumb',
            'calculated_selling_denied',
            'selling_denied',
            'availability',
            'availability_status',
            'availability_dispatch_time',
            'is_main_variant',
            'is_variant',
            'detail_url',
            'visibility',
            'uuid',
            'unit',
            'stock_quantity',
            'variants',
            'has_preorder',
            'main_variant_id',
            'seo_h1',
            'seo_title',
            'seo_meta_description',
            'accessories',
            'name_prefix',
            'name_sufix',
            'is_sale_exclusion',
            'product_available_stores_count_information',
            'product_videos',
            'store_availabilities_information',
            'files',
            'usps',
            'searching_names',
            'searching_descriptions',
            'searching_catnums',
            'searching_eans',
            'searching_partnos',
            'searching_short_descriptions',
            'slug',
            'available_stores_count',
            'related_products',
        ];
    }
}

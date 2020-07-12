<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Elasticsearch;

use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ProductExportRepositoryTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository
     * @inject
     */
    private $repository;

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
            'name',
            'name_prefix',
            'name_sufix',
            'catnum',
            'partno',
            'ean',
            'description',
            'short_description',
            'availability',
            'brand',
            'flags',
            'main_category_path',
            'categories',
            'detail_url',
            'in_stock',
            'prices',
            'parameters',
            'ordering_priority',
            'calculated_selling_denied',
            'selling_denied',
            'is_main_variant',
            'visibility',
            'uuid',
            'unit',
            'is_using_stock',
            'stock_quantity',
            'variants',
            'main_variant_id',
            'non_selling_price',
            'is_in_sale',
            'is_sale_exclusion',
            'product_available_stocks_count_information',
            'product_count_exposed_in_stores',
            'variants_parameters_setup',
            'searching_names',
            'searching_descriptions',
            'searching_catnums',
            'searching_eans',
            'searching_partnos',
            'searching_short_descriptions',
        ];
    }
}

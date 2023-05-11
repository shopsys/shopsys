<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ProductExportRepositoryTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

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
            'name',
            'catnum',
            'partno',
            'ean',
            'description',
            'short_description',
            'availability',
            'availability_dispatch_time',
            'brand',
            'brand_name',
            'brand_url',
            'flags',
            'categories',
            'main_category_id',
            'detail_url',
            'in_stock',
            'prices',
            'parameters',
            'ordering_priority',
            'calculated_selling_denied',
            'selling_denied',
            'is_variant',
            'is_main_variant',
            'visibility',
            'uuid',
            'unit',
            'is_using_stock',
            'stock_quantity',
            'accessories',
            'variants',
            'main_variant_id',
            'seo_h1',
            'seo_title',
            'seo_meta_description',
        ];
    }
}

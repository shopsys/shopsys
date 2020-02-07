<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ExportWithFilterRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

class ExportWithFilterRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ExportWithFilterRepository
     * @inject
     */
    private $repository;

    public function testProductDataHaveExpectedStructure(): void
    {
        $data = $this->repository->getProductsData($this->domain->getId(), $this->domain->getLocale(), 0, 10);
        $this->assertCount(10, $data);

        $structure = array_keys(reset($data));
        sort($structure);

        $expectedStructure = $this->getExpectedStructureForRepository($this->repository);

        sort($expectedStructure);

        $this->assertSame($expectedStructure, $structure);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ExportWithFilterRepository $exportWithFilterRepository
     *
     * @return string[]
     */
    private function getExpectedStructureForRepository(ExportWithFilterRepository $exportWithFilterRepository): array
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
            'brand',
            'flags',
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
            'main_variant',
        ];
    }
}

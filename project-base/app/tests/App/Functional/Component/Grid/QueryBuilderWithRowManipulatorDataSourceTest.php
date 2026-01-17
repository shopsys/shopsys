<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Grid;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;
use Tests\App\Test\TransactionFunctionalTestCase;

class QueryBuilderWithRowManipulatorDataSourceTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    protected QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequest();
    }

    public function testGetOneRow(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p');

        $dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create($qb, 'p.id', function ($row) {
            $row['newField'] = 'newValue';

            return $row;
        });

        $row = $dataSource->getOneRow($this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class)->getId());

        $this->assertIsArray($row);
        $this->assertArrayHasKey('newField', $row);
        $this->assertSame('newValue', $row['newField']);
    }

    public function testGetTotalRowsCount(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->where('p.id >= 1 AND p.id <= 10')
            ->setFirstResult(8)
            ->setMaxResults(5);

        $dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create($qb, 'p.id', function ($row) {
            $row['newField'] = 'newValue' . $row['p']['id'];

            return $row;
        });

        $count = $dataSource->getTotalRowsCount();

        $this->assertSame(10, $count);
    }

    public function testGetRows(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->setMaxResults(5);

        $dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create($qb, 'p.id', function ($row) {
            $row['newField'] = 'newValue' . $row['p']['id'];

            return $row;
        });

        $rows = $dataSource->getPaginatedRows()->getResults();
        $this->assertIsArray($rows);
        $this->assertCount(5, $rows);

        foreach ($rows as $row) {
            $this->assertIsArray($row);
            $this->assertArrayHasKey('newField', $row);
            $this->assertSame('newValue' . $row['p']['id'], $row['newField']);
        }
    }
}

<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Grid;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Tests\App\Test\TransactionFunctionalTestCase;

class QueryBuilderWithRowManipulatorDataSourceTest extends TransactionFunctionalTestCase
{
    public function testGetOneRow()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p');

        $dataSource = new QueryBuilderWithRowManipulatorDataSource($qb, 'p.id', function ($row) {
            $row['newField'] = 'newValue';
            return $row;
        });

        /** @phpstan-ignore-next-line */
        $row = $dataSource->getOneRow($this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1'));

        $this->assertIsArray($row);
        $this->assertArrayHasKey('newField', $row);
        $this->assertSame('newValue', $row['newField']);
    }

    public function testGetTotalRowsCount()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->where('p.id >= 1 AND p.id <= 10')
            ->setFirstResult(8)
            ->setMaxResults(5);

        $dataSource = new QueryBuilderWithRowManipulatorDataSource($qb, 'p.id', function ($row) {
            $row['newField'] = 'newValue' . $row['p']['id'];
            return $row;
        });

        $count = $dataSource->getTotalRowsCount();

        $this->assertSame(10, $count);
    }

    public function testGetRows()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->setMaxResults(5);

        $dataSource = new QueryBuilderWithRowManipulatorDataSource($qb, 'p.id', function ($row) {
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

<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Grid;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Tests\App\Test\TransactionFunctionalTestCase;

class QueryBuilderDataSourceTest extends TransactionFunctionalTestCase
{
    public function testGetOneRow()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p');

        $queryBuilderDataSource = new QueryBuilderDataSource($qb, 'p.id');

        $row = $queryBuilderDataSource->getOneRow($this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1'));

        $this->assertIsArray($row);
        $this->assertArrayHasKey('p', $row);
    }

    public function testGetTotalRowsCount()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->where('p.id >= 1 AND p.id <= 10')
            ->setFirstResult(8)
            ->setMaxResults(5);

        $queryBuilderDataSource = new QueryBuilderDataSource($qb, 'p.id');

        $count = $queryBuilderDataSource->getTotalRowsCount();

        $this->assertSame(10, $count);
    }

    public function testGetRows()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->setMaxResults(5);

        $queryBuilderDataSource = new QueryBuilderDataSource($qb, 'p.id');

        $rows = $queryBuilderDataSource->getPaginatedRows()->getResults();
        $this->assertIsArray($rows);
        $this->assertCount(5, $rows);

        foreach ($rows as $row) {
            $this->assertIsArray($row);
            $this->assertArrayHasKey('p', $row);
        }
    }

    public function testGetRowsInAscOrder()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->setMaxResults(10);

        $queryBuilderDataSource = new QueryBuilderDataSource($qb, 'p.id');

        $rows = $queryBuilderDataSource->getPaginatedRows(null, 1, 'p.id', QueryBuilderDataSource::ORDER_ASC)->getResults();
        $this->assertCount(10, $rows);

        $lastId = null;
        foreach ($rows as $row) {
            if ($lastId === null) {
                $lastId = $row['p']['id'];
            } else {
                $this->assertGreaterThan($lastId, $row['p']['id']);
            }
        }
    }

    public function testGetRowsInDescOrder()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->setMaxResults(10);

        $queryBuilderDataSource = new QueryBuilderDataSource($qb, 'p.id');

        $rows = $queryBuilderDataSource->getPaginatedRows(null, 1, 'p.id', QueryBuilderDataSource::ORDER_DESC)->getResults();
        $this->assertCount(10, $rows);

        $lastId = null;
        foreach ($rows as $row) {
            if ($lastId === null) {
                $lastId = $row['p']['id'];
            } else {
                $this->assertLessThan($lastId, $row['p']['id']);
            }
        }
    }
}

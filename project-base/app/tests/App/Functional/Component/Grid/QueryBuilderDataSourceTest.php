<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Grid;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Tests\App\Test\TransactionFunctionalTestCase;

class QueryBuilderDataSourceTest extends TransactionFunctionalTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequest();
    }

    /**
     * @inject
     */
    protected QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory;

    public function testGetOneRow(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p');

        $queryBuilderDataSource = $this->queryBuilderDataSourceFactory->create($qb, 'p.id');

        $row = $queryBuilderDataSource->getOneRow($this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class)->getId());

        $this->assertIsArray($row);
        $this->assertArrayHasKey('p', $row);
    }

    public function testGetTotalRowsCount(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->where('p.id >= 1 AND p.id <= 10')
            ->setFirstResult(8)
            ->setMaxResults(5);

        $queryBuilderDataSource = $this->queryBuilderDataSourceFactory->create($qb, 'p.id');

        $count = $queryBuilderDataSource->getTotalRowsCount();

        $this->assertSame(10, $count);
    }

    public function testGetRows(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->setMaxResults(5);

        $queryBuilderDataSource = $this->queryBuilderDataSourceFactory->create($qb, 'p.id');

        $rows = $queryBuilderDataSource->getPaginatedRows()->getResults();
        $this->assertIsArray($rows);
        $this->assertCount(5, $rows);

        foreach ($rows as $row) {
            $this->assertIsArray($row);
            $this->assertArrayHasKey('p', $row);
        }
    }

    public function testGetRowsInAscOrder(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->setMaxResults(10);

        $queryBuilderDataSource = $this->queryBuilderDataSourceFactory->create($qb, 'p.id');

        $rows = $queryBuilderDataSource->getPaginatedRows(
            null,
            1,
            'p.id',
            QueryBuilderDataSource::ORDER_ASC,
        )->getResults();
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

    public function testGetRowsInDescOrder(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->setMaxResults(10);

        $queryBuilderDataSource = $this->queryBuilderDataSourceFactory->create($qb, 'p.id');

        $rows = $queryBuilderDataSource->getPaginatedRows(
            null,
            1,
            'p.id',
            QueryBuilderDataSource::ORDER_DESC,
        )->getResults();
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

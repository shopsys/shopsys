<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Stock\Exception\StockNotFoundException;

class StockRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    protected function getStockRepository(): EntityRepository
    {
        return $this->em->getRepository(Stock::class);
    }

    public function getById(int $stockId): Stock
    {
        $stock = $this->getStockRepository()->find($stockId);

        if ($stock === null) {
            throw new StockNotFoundException();
        }

        return $stock;
    }

    /**
     * @param int[] $stockIds
     * @return \Shopsys\FrameworkBundle\Model\Stock\Stock[]
     */
    public function getStocksByIdsIndexedById(array $stockIds): array
    {
        return $this->getStockRepository()
            ->createQueryBuilder('s', 's.id')
            ->where('s.id IN (:stockIds)')
            ->setParameter('stockIds', $stockIds)
            ->getQuery()
            ->getResult();
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('s')
            ->from(Stock::class, 's');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\Stock[]
     */
    public function getStocksEnabledOnDomain(int $domainId): array
    {
        return $this->getQueryBuilder()
            ->join('s.domains', 'sd', Join::WITH, 'sd.isEnabled = TRUE AND sd.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\Stock[]
     */
    public function getAllStocks(): array
    {
        return $this->getStockRepository()->findBy([], ['position' => 'ASC']);
    }

    public function getAllStocksQueryBuilder(): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->orderBy('s.position', 'ASC');
    }

    public function findStockByExternalId(string $externalId): ?Stock
    {
        return $this->getStockRepository()->findOneBy(['externalId' => $externalId]);
    }

    public function changeDefaultStock(Stock $stock): void
    {
        $this->em->createQueryBuilder()
            ->update(Stock::class, 's')
            ->set('s.isDefault', 'FALSE')
            ->getQuery()
            ->execute();

        $stock->setDefault();
        $this->em->flush();
    }

    public function getCount(): int
    {
        return $this->getQueryBuilder()
            ->select('count(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}

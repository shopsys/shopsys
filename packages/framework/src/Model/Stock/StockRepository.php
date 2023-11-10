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
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getStockRepository(): EntityRepository
    {
        return $this->em->getRepository(Stock::class);
    }

    /**
     * @param int $stockId
     * @return \Shopsys\FrameworkBundle\Model\Stock\Stock
     */
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

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('s')
            ->from(Stock::class, 's');
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Stock\Stock[]
     */
    public function getStocksEnabledOnDomain(int $domainId): array
    {
        return $this->getQueryBuilder()
            ->join(StockDomain::class, 'sd', Join::WITH, 's.id = sd.stock AND sd.isEnabled = TRUE AND sd.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->execute();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\Stock[]
     */
    public function getAllStocks(): array
    {
        return $this->getStockRepository()->findBy([], ['position' => 'ASC']);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllStocksQueryBuilder(): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->orderBy('s.position', 'ASC');
    }

    /**
     * @param string $externalId
     * @return \Shopsys\FrameworkBundle\Model\Stock\Stock|null
     */
    public function findStockByExternalId(string $externalId): ?Stock
    {
        return $this->getStockRepository()->findOneBy(['externalId' => $externalId]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\Stock $stock
     */
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
}

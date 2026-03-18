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

    /**
     * @return array<int, int[]> stock ID => array of domain IDs where stock is default
     */
    public function getDefaultDomainIdsIndexedByStockId(): array
    {
        $rows = $this->em->createQueryBuilder()
            ->select('IDENTITY(sd.stock) AS stockId, sd.domainId')
            ->from(StockDomain::class, 'sd')
            ->where('sd.isDefault = true')
            ->getQuery()
            ->getArrayResult();

        $result = [];

        foreach ($rows as $row) {
            $result[(int)$row['stockId']][] = (int)$row['domainId'];
        }

        return $result;
    }

    public function findStockByExternalId(string $externalId): ?Stock
    {
        return $this->getStockRepository()->findOneBy(['externalId' => $externalId]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\StockDomain[]
     */
    public function getDefaultStockDomainsForDomainExcept(?int $stockId, int $domainId): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('sd')
            ->from(StockDomain::class, 'sd')
            ->where('sd.domainId = :domainId')
            ->andWhere('sd.isDefault = true')
            ->setParameter('domainId', $domainId);

        if ($stockId !== null) {
            $qb->andWhere('sd.stock != :stockId')
                ->setParameter('stockId', $stockId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int[] $allDomainIds
     * @return int[] domain IDs that have no default stock
     */
    public function getDomainIdsWithoutDefaultStock(array $allDomainIds): array
    {
        $domainIdsWithDefault = array_map(
            static fn (array $row) => (int)$row['domainId'],
            $this->em->createQueryBuilder()
                ->select('sd.domainId')
                ->from(StockDomain::class, 'sd')
                ->where('sd.isDefault = true')
                ->getQuery()
                ->getArrayResult(),
        );

        return array_values(array_diff($allDomainIds, $domainIdsWithDefault));
    }

    /**
     * @return array{stockId: int, stockName: string, domainId: int}[]
     */
    public function getDefaultButDisabledStockDomains(): array
    {
        return $this->em->createQueryBuilder()
            ->select('s.id AS stockId, s.name AS stockName, sd.domainId')
            ->from(StockDomain::class, 'sd')
            ->join('sd.stock', 's')
            ->where('sd.isDefault = true')
            ->andWhere('sd.isEnabled = false')
            ->getQuery()
            ->getArrayResult();
    }

    public function getCount(): int
    {
        return $this->getQueryBuilder()
            ->select('count(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}

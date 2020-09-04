<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Model\Stock\Exception\StockNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class StockRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getStockRepository(): EntityRepository
    {
        return $this->em->getRepository(Stock::class);
    }

    /**
     * @param int $stockId
     * @return \App\Model\Stock\Stock
     */
    public function getById($stockId): Stock
    {
        $stock = $this->getStockRepository()->find($stockId);
        if ($stock === null) {
            throw new StockNotFoundException();
        }
        return $stock;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('s')
            ->from(Stock::class, 's');
    }

    /**
     * @return \App\Model\Stock\Stock[]
     */
    public function getAllStocks(): array
    {
        return $this->getStockRepository()->findBy([], ['domainId' => 'ASC', 'position' => 'ASC']);
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderByDomain(int $domainId): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->where('s.domainId = :domainId')
            ->orderBy('s.domainId', 'ASC')
            ->orderBy('s.position', 'ASC')
            ->setParameter('domainId', $domainId);
    }

    /**
     * @param string $name
     * @param int $domainId
     * @return \App\Model\Stock\Stock|null
     */
    public function findStockByNameAndDomainId(string $name, int $domainId): ?Stock
    {
        return $this->getQueryBuilderByDomain($domainId)
            ->andWhere('s.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $externalId
     * @return \App\Model\Stock\Stock|null
     */
    public function findStockByExternalId(string $externalId): ?Stock
    {
        return $this->getStockRepository()->findOneBy(['externalId' => $externalId]);
    }

    /**
     * @param int $domainId
     * @return \App\Model\Stock\Stock[]
     */
    public function getStocksByDomainId(int $domainId): array
    {
        return $this->getQueryBuilderByDomain($domainId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $domainId
     * @return \App\Model\Stock\Stock[]
     */
    public function getStocksWithoutCentralByDomainId(int $domainId): array
    {
        return $this->getQueryBuilderByDomain($domainId)
            ->andWhere('s.centralStock = false')
            ->getQuery()
            ->execute();
    }
}

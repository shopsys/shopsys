<?php

declare(strict_types=1);


namespace App\Model\Stock;

use Doctrine\ORM\EntityManagerInterface;
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
    protected function getStockRepository()
    {
        return $this->em->getRepository(Stock::class);
    }

    /**
     * @param $stockId
     * @return \App\Model\Stock\Stock|null
     */
    public function findById($stockId)
    {
        return $this->getStockRepository()->find($stockId);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getStockQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('s')
            ->from(Stock::class, 's');
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getStockByDomainQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->getStockQueryBuilder()
            ->where('s.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }

    /**
     * @param int $domainId
     * @return \App\Model\Stock\Stock[]
     */
    public function getAllStockByDomain(int $domainId): array
    {
        return $this->getStockByDomainQueryBuilder($domainId)
            ->getQuery()->execute();
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getAllStockCountByDomainId(int $domainId):int
    {
        return $this->getStockByDomainQueryBuilder($domainId)
            ->select('COUNT(s)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
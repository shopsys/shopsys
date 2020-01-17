<?php

declare(strict_types=1);


namespace App\Model\Product\Series;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;

class ProductSeriesRepository
{

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;
    }

    /**
     * @return \App\Model\Product\Series\ProductSeriesRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository(){
        return $this->em->getRepository(ProductSeries::class);
    }

    /**
     * @param $id
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function findById($id): ProductSeries
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder{
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('ps')
            ->from(ProductSeries::class, 'ps');
        return $queryBuilder;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getVisibleQueryBuilder(): QueryBuilder{

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->join(ProductSeriesDomain::class, 'psd',Join::WITH, 'psd.productSeries = ps.id');
        $queryBuilder->where('psd.hidden = FALSE');

        return $queryBuilder;
    }

    /**
     * @param int $id
     * @param int $domainId
     * @return \App\Model\Product\Series\ProductSeries|null
     */
    public function findVisibleProductSeriesById(int $id, int $domainId): ?ProductSeries
    {

        $queryBuilder = $this->getVisibleQueryBuilder();
        $queryBuilder->andWhere('psd.domainId = :domainId');
        $queryBuilder->andWhere('ps.id = :id');

        $queryBuilder->setParameter('domainId', $domainId);
        $queryBuilder->setParameter('id', $id);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }



}
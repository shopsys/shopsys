<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use App\Model\Product\Series\Exception\ProductSeriesNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ProductSeriesRepository
{
    public const DEFAULT_LOCALE = 'cs';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    /**
     * @return \App\Model\Product\Series\ProductSeriesRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        return $this->em->getRepository(ProductSeries::class);
    }

    /**
     * @param $id
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function findById($id): ProductSeries
    {
        $productSeries = $this->getRepository()->find($id);
        if ($productSeries === null) {
            $message = 'Product series with ID ' . $id . ' not found';
            throw new ProductSeriesNotFoundException($message);
        }
        return $productSeries;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('ps')
            ->from(ProductSeries::class, 'ps');
        return $queryBuilder;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getVisibleQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->join(ProductSeriesDomain::class, 'psd', Join::WITH, 'psd.productSeries = ps.id');
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

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllProductSeriesQueryBuilderByMainDomain(): QueryBuilder
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->join(ProductSeriesDomain::class, 'psd', Join::WITH, 'psd.productSeries = ps.id');
        $queryBuilder->join(ProductSeriesTranslation::class, 'pst', Join::WITH, 'pst.translatable = ps.id');
        $queryBuilder->select('pst', 'ps');
        $queryBuilder->andWhere('psd.domainId = :domainId');
        $queryBuilder->andWhere('pst.locale = :locale');
        $queryBuilder->orderBy('ps.id', 'DESC');

        $queryBuilder->setParameter('domainId', Domain::MAIN_ADMIN_DOMAIN_ID);
        $queryBuilder->setParameter('locale', self::DEFAULT_LOCALE);
        return $queryBuilder;
    }

    /**
     * @param int $domainId
     * @return array
     */
    public function getAllVisibleProductSeriesByDomain(int $domainId): array
    {
        $queryBuilder = $this->getVisibleQueryBuilder();
        $queryBuilder->andWhere('psd.domainId = :domainId');
        $queryBuilder->setParameter('domainId', $domainId);

        $queryBuilder->orderBy('ps.id', 'DESC');

        return $queryBuilder->getQuery()->execute();
    }
}

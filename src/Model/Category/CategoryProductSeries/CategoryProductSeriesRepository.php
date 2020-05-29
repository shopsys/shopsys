<?php

declare(strict_types=1);

namespace App\Model\Category\CategoryProductSeries;

use App\Model\Category\Category;
use App\Model\Product\Series\ProductSeries;
use App\Model\Product\Series\ProductSeriesDomain;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class CategoryProductSeriesRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCategoryProductSeriesRepository()
    {
        return $this->em->getRepository(CategoryProductSeries::class);
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Product\Series\ProductSeries[]
     */
    public function getAllAssignedProductSeriesByCategory(Category $category)
    {
        $queryBuilder = $this->getAllAssignedProductSeriesByCategoryQueryBuilder($category);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param int $domainId
     * @return \App\Model\Product\Series\ProductSeries[]
     */
    public function getVisibleProductSeriesByCategoryAndDomainId(Category $category, int $domainId): array
    {
        $queryBuilder = $this->getAllAssignedProductSeriesByCategoryQueryBuilder($category);
        $this->filterOnlyVisibleProductSeriesByDomainId($queryBuilder, $domainId);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param int $domainId
     */
    private function filterOnlyVisibleProductSeriesByDomainId(QueryBuilder $queryBuilder, int $domainId): void
    {
        $queryBuilder->join(ProductSeriesDomain::class, 'psd', Join::WITH, 'psd.productSeries = ps AND psd.domainId = :domainId');
        $queryBuilder->andWhere('psd.hidden = false');
        $queryBuilder->setParameter('domainId', $domainId);
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getAllAssignedProductSeriesByCategoryQueryBuilder(Category $category): QueryBuilder
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('ps')
            ->from(ProductSeries::class, 'ps')
            ->join(CategoryProductSeries::class, 'cps', Join::WITH, 'cps.productSeries = ps')
            ->where('cps.category = :category')
            ->orderBy('cps.position')
            ->setParameters([
                'category' => $category,
            ]);

        return $queryBuilder;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Product\Series\ProductSeries[]
     */
    public function getAllProductSeriesByCategory(Category $category)
    {
        return $this->getCategoryProductSeriesRepository()->findBy(['category' => $category->getId()]);
    }
}

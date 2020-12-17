<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Category;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryDomain;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository as FrameworkCategoryRepository;

class CategoryRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryRepository
     */
    protected FrameworkCategoryRepository $categoryRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     */
    public function __construct(EntityManagerInterface $em, FrameworkCategoryRepository $categoryRepository)
    {
        $this->em = $em;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param string $searchText
     * @param string $locale
     * @param int $domainId
     * @param int $offset
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleCategoriesBySearchText(string $searchText, string $locale, int $domainId, int $offset, int $limit): array
    {
        $queryBuilder = $this->getVisibleCategoriesBySearchTextQueryBuilder($searchText, $locale, $domainId);

        $queryBuilder
            ->orderBy('ct.name')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param string $searchText
     * @param string $locale
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getVisibleCategoriesBySearchTextQueryBuilder(string $searchText, string $locale, int $domainId): QueryBuilder
    {
        $queryBuilder = $this->getAllVisibleByDomainIdQueryBuilder($domainId);

        $this->categoryRepository->addTranslationPublic($queryBuilder, $locale);
        $this->categoryRepository->filterBySearchTextPublic($queryBuilder, $searchText);

        return $queryBuilder;
    }

    /**
     * @param string $searchText
     * @param string $locale
     * @param int $domainId
     * @return int
     */
    public function getVisibleCategoriesBySearchTextCount(string $searchText, string $locale, int $domainId): int
    {
        $queryBuilder =
            $this->getVisibleCategoriesBySearchTextQueryBuilder($searchText, $locale, $domainId)
                ->select('COUNT(c)');

        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getAllVisibleByDomainIdQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->em->getRepository(Category::class)->createQueryBuilder('c')
            ->where('c.parent IS NOT NULL')
            ->join(CategoryDomain::class, 'cd', Join::WITH, 'cd.category = c.id')
            ->andWhere('cd.domainId = :domainId')
            ->andWhere('cd.visible = TRUE')
            ->setParameter('domainId', $domainId);
    }
}

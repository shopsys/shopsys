<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Category;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryDomain;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository as FrameworkCategoryRepository;

class CategoryRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly FrameworkCategoryRepository $categoryRepository,
    ) {
    }

    /**
     * @param string $searchText
     * @param string $locale
     * @param int $domainId
     * @param int $offset
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleCategoriesBySearchText(
        string $searchText,
        string $locale,
        int $domainId,
        int $offset,
        int $limit,
    ): array {
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
    protected function getVisibleCategoriesBySearchTextQueryBuilder(
        string $searchText,
        string $locale,
        int $domainId,
    ): QueryBuilder {
        $queryBuilder = $this->getAllVisibleByDomainIdQueryBuilder($domainId);

        $this->categoryRepository->addTranslation($queryBuilder, $locale);
        $this->categoryRepository->filterBySearchText($queryBuilder, $searchText);

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

    /**
     * @param int[][] $categoriesIds
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[][]
     */
    public function getVisibleCategoriesByIds(array $categoriesIds, DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId())
            ->addSelect('cd')
            ->andWhere('c.id IN(:categoryIds)')
            ->indexBy('c', 'c.id')
            ->setParameter('categoryIds', array_merge(...$categoriesIds));
        $this->categoryRepository->addTranslation($queryBuilder, $domainConfig->getLocale());
        $result = $queryBuilder->getQuery()->execute();

        $allCategories = [];

        foreach ($categoriesIds as $key => $categoryIds) {
            $allCategories[$key] = [];

            foreach ($categoryIds as $categoryId) {
                if (!array_key_exists($categoryId, $result)) {
                    continue;
                }

                $allCategories[$key][] = $result[$categoryId];
            }
        }

        return array_values($allCategories);
    }
}

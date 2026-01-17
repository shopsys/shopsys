<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Category;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryDomain;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository as FrameworkCategoryRepository;

class CategoryRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly FrameworkCategoryRepository $categoryRepository,
        protected readonly OrderByCollationHelper $orderByCollationHelper,
    ) {
    }

    /**
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
            ->orderBy($this->orderByCollationHelper->createOrderByForLocale('ct.name', $locale))
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->execute();
    }

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

    public function getVisibleCategoriesBySearchTextCount(string $searchText, string $locale, int $domainId): int
    {
        $queryBuilder =
            $this->getVisibleCategoriesBySearchTextQueryBuilder($searchText, $locale, $domainId)
                ->select('COUNT(c)');

        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }

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

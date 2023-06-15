<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Category;

use App\Component\Doctrine\OrderByCollationHelper;
use App\Model\Category\LinkedCategory\LinkedCategory;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrontendApiBundle\Model\Category\CategoryRepository as BaseCategoryRepository;

/**
 * @property \App\Model\Category\CategoryRepository $categoryRepository
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Category\CategoryRepository $categoryRepository)
 */
class CategoryRepository extends BaseCategoryRepository
{
    /**
     * @param string $searchText
     * @param string $locale
     * @param int $domainId
     * @param int $offset
     * @param int $limit
     * @return \App\Model\Category\Category[]
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
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('ct.name', $locale))
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \App\Model\Category\Category[] $categories
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Category\Category[][]
     */
    public function getAllVisibleChildrenByCategoriesAndDomainConfig(
        array $categories,
        DomainConfig $domainConfig,
    ): array {
        $childrenByCategories = [];

        foreach ($categories as $category) {
            $childrenByCategories[$category->getId()] = [];
        }
        $queryBuilder = $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId())
            ->addSelect('cd')
            ->andWhere('c.parent IN(:categories)')
            ->setParameter('categories', $categories);
        $this->categoryRepository->addTranslation($queryBuilder, $domainConfig->getLocale());

        /** @var \App\Model\Category\Category $childCategory */
        foreach ($queryBuilder->getQuery()->execute() as $childCategory) {
            $childrenByCategories[$childCategory->getParent()->getId()][] = $childCategory;
        }

        return array_values($childrenByCategories);
    }

    /**
     * @param \App\Model\Category\Category[] $parentCategories
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Category\Category[][]
     */
    public function getVisibleLinkedCategories(array $parentCategories, DomainConfig $domainConfig): array
    {
        $visibleLinkedCategories = [];

        foreach ($parentCategories as $parentCategory) {
            $visibleLinkedCategories[$parentCategory->getId()] = [];
        }
        $linkedCategories = $this->em->createQueryBuilder()
            ->select('lc, c, cd, ct')
            ->from(LinkedCategory::class, 'lc')
            ->join('lc.category', 'c', Join::WITH, 'c.parent IS NOT NULL')
            ->join('c.domains', 'cd', Join::WITH, 'cd.domainId = :domainId')
            ->join('c.translations', 'ct', Join::WITH, 'ct.locale = :locale')
            ->where('lc.parentCategory IN(:parentCategories)')
            ->andWhere('cd.visible = true')
            ->setParameter('parentCategories', $parentCategories)
            ->setParameter('domainId', $domainConfig->getId())
            ->setParameter('locale', $domainConfig->getLocale())
            ->orderBy('lc.position')
            ->getQuery()->execute();

        /** @var \App\Model\Category\LinkedCategory\LinkedCategory $linkedCategory */
        foreach ($linkedCategories as $linkedCategory) {
            $visibleLinkedCategories[$linkedCategory->getParentCategory()->getId()][] = $linkedCategory->getCategory();
        }

        return array_values($visibleLinkedCategories);
    }

    /**
     * @param int[][] $categoriesIds
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Category\Category[][]
     */
    public function getCategoriesByIds(array $categoriesIds, DomainConfig $domainConfig): array
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

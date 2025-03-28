<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Category;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrontendApiBundle\Model\Category\CategoryRepository as BaseCategoryRepository;

/**
 * @property \App\Model\Category\CategoryRepository $categoryRepository
 * @method \App\Model\Category\Category[][] getVisibleCategoriesByIds(int[][] $categoriesIds, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Category\CategoryRepository $categoryRepository, \Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper $orderByCollationHelper)
 * @method \App\Model\Category\Category[] getVisibleCategoriesBySearchText(string $searchText, string $locale, int $domainId, int $offset, int $limit)
 */
class CategoryRepository extends BaseCategoryRepository
{
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
}

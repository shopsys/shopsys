<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Category;

use App\Component\Doctrine\OrderByCollationHelper;
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
    public function getVisibleCategoriesBySearchText(string $searchText, string $locale, int $domainId, int $offset, int $limit): array
    {
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
    public function getAllVisibleChildrenByCategoriesAndDomainConfig(array $categories, DomainConfig $domainConfig): array
    {
        $childrenByCategories = [];
        foreach ($categories as $category) {
            $childrenByCategories[$category->getId()] = [];
        }
        $queryBuilder = $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId())
            ->addSelect('cd')
            ->andWhere('c.parent IN(:categories)')
            ->setParameter('categories', $categories);
        $this->categoryRepository->addTranslationPublic($queryBuilder, $domainConfig->getLocale());

        /** @var \App\Model\Category\Category $childCategory */
        foreach ($queryBuilder->getQuery()->execute() as $childCategory) {
            $childrenByCategories[$childCategory->getParent()->getId()][] = $childCategory;
        }

        return array_values($childrenByCategories);
    }
}

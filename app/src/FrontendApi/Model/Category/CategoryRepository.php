<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Category;

use App\Component\Doctrine\OrderByCollationHelper;
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
}

<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Category;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrontendApiBundle\Model\Category\CategoryFacade as BaseCategoryFacade;

/**
 * @property \App\FrontendApi\Model\Category\CategoryRepository $categoryRepository
 * @method __construct(\App\FrontendApi\Model\Category\CategoryRepository $categoryRepository)
 * @method \App\Model\Category\Category[] getVisibleCategoriesBySearchText(string $search, string $locale, int $domainId, int $offset, int $limit)
 * @method \App\Model\Category\Category[][] getVisibleCategoriesByIds(int[][] $categoriesIds, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 */
class CategoryFacade extends BaseCategoryFacade
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
        return $this->categoryRepository->getAllVisibleChildrenByCategoriesAndDomainConfig($categories, $domainConfig);
    }

    /**
     * @param \App\Model\Category\Category[] $parentCategories
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Category\Category[][]
     */
    public function getVisibleLinkedCategories(array $parentCategories, DomainConfig $domainConfig): array
    {
        return $this->categoryRepository->getVisibleLinkedCategories($parentCategories, $domainConfig);
    }
}

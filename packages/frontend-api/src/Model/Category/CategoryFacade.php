<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Category;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class CategoryFacade
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Category\CategoryRepository $categoryRepository
     */
    public function __construct(protected readonly CategoryRepository $categoryRepository)
    {
    }

    /**
     * @param string $search
     * @param string $locale
     * @param int $domainId
     * @param int $offset
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleCategoriesBySearchText(
        string $search,
        string $locale,
        int $domainId,
        int $offset,
        int $limit,
    ): array {
        return $this->categoryRepository->getVisibleCategoriesBySearchText(
            $search,
            $locale,
            $domainId,
            $offset,
            $limit,
        );
    }

    /**
     * @param string $searchText
     * @param string $locale
     * @param int $domainId
     * @return int
     */
    public function getVisibleCategoriesBySearchTextCount(string $searchText, string $locale, int $domainId): int
    {
        return $this->categoryRepository->getVisibleCategoriesBySearchTextCount($searchText, $locale, $domainId);
    }

    /**
     * @param int[][] $categoriesIds
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[][]
     */
    public function getVisibleCategoriesByIds(array $categoriesIds, DomainConfig $domainConfig): array
    {
        return $this->categoryRepository->getVisibleCategoriesByIds($categoriesIds, $domainConfig);
    }
}

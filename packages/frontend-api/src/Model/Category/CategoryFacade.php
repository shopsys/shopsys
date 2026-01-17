<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Category;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class CategoryFacade
{
    public function __construct(protected readonly CategoryRepository $categoryRepository)
    {
    }

    /**
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

    public function getVisibleCategoriesBySearchTextCount(string $searchText, string $locale, int $domainId): int
    {
        return $this->categoryRepository->getVisibleCategoriesBySearchTextCount($searchText, $locale, $domainId);
    }

    /**
     * @param int[][] $categoriesIds
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[][]
     */
    public function getVisibleCategoriesByIds(array $categoriesIds, DomainConfig $domainConfig): array
    {
        return $this->categoryRepository->getVisibleCategoriesByIds($categoriesIds, $domainConfig);
    }
}

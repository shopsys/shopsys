<?php

declare(strict_types=1);

namespace App\Model\Navigation;

class NavigationItemDetailFactory
{
    /**
     * @var \App\Model\Navigation\NavigationItemCategoryFacade
     */
    private NavigationItemCategoryFacade $navigationItemCategoryFacade;

    /**
     * @param \App\Model\Navigation\NavigationItemCategoryFacade $navigationItemCategoryFacade
     */
    public function __construct(
        NavigationItemCategoryFacade $navigationItemCategoryFacade
    ) {
        $this->navigationItemCategoryFacade = $navigationItemCategoryFacade;
    }

    /**
     * @param \App\Model\Navigation\NavigationItem[] $navigationItems
     * @param int $domainId
     * @return \App\Model\Navigation\NavigationItemDetail[]
     */
    public function createDetails(array $navigationItems, int $domainId): array
    {
        $details = [];

        foreach ($navigationItems as $navigationItem) {
            $details[] = $this->createDetail($navigationItem, $domainId);
        }

        return $details;
    }

    /**
     * @param \App\Model\Navigation\NavigationItem $navigationItem
     * @param int $domainId
     * @return \App\Model\Navigation\NavigationItemDetail
     */
    private function createDetail(NavigationItem $navigationItem, int $domainId): NavigationItemDetail
    {
        $categoriesByColumnNumber = $this->navigationItemCategoryFacade
            ->getSortedVisibleCategoriesIndexedByColumnNumberForNavigationItem($navigationItem, $domainId);

        return new NavigationItemDetail(
            $navigationItem,
            $categoriesByColumnNumber
        );
    }
}

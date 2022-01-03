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
        $categoriesIndexedByNavigationItemIdAndColumnNumber = $this->navigationItemCategoryFacade
            ->getSortedVisibleCategoriesIndexedByNavigationItemIdAndColumnNumber($navigationItems, $domainId);

        foreach ($navigationItems as $navigationItem) {
            if (!isset($categoriesIndexedByNavigationItemIdAndColumnNumber[$navigationItem->getId()])) {
                continue;
            }
            $details[] = new NavigationItemDetail(
                $navigationItem,
                $categoriesIndexedByNavigationItemIdAndColumnNumber[$navigationItem->getId()]
            );
        }

        return $details;
    }
}

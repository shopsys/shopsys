<?php

declare(strict_types=1);

namespace App\Model\Navigation;

class NavigationItemDataFactory
{
    /**
     * @param \App\Model\Navigation\NavigationItemCategoryFacade $navigationItemCategoryFacade
     */
    public function __construct(
        private NavigationItemCategoryFacade $navigationItemCategoryFacade,
    ) {
    }

    /**
     * @return \App\Model\Navigation\NavigationItemData
     */
    public function createNew(): NavigationItemData
    {
        return new NavigationItemData();
    }

    /**
     * @param \App\Model\Navigation\NavigationItem $navigationItem
     * @return \App\Model\Navigation\NavigationItemData
     */
    public function createForEntity(NavigationItem $navigationItem): NavigationItemData
    {
        $navigationItemData = new NavigationItemData();
        $navigationItemData->name = $navigationItem->getName();
        $navigationItemData->url = $navigationItem->getUrl();
        $navigationItemData->domainId = $navigationItem->getDomainId();

        $navigationItemData->categoriesByColumnNumber = $this->navigationItemCategoryFacade
            ->getSortedCategoriesIndexedByColumnNumberForNavigationItem($navigationItem);

        return $navigationItemData;
    }
}

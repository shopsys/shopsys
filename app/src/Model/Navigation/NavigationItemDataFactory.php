<?php

declare(strict_types=1);

namespace App\Model\Navigation;

class NavigationItemDataFactory
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

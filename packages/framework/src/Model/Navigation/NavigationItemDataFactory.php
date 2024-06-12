<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

class NavigationItemDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemCategoryFacade $navigationItemCategoryFacade
     */
    public function __construct(
        protected NavigationItemCategoryFacade $navigationItemCategoryFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData
     */
    public function createNew(): NavigationItemData
    {
        return new NavigationItemData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem $navigationItem
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData
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

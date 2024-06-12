<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

class NavigationItemDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemCategoryFacade $navigationItemCategoryFacade
     */
    public function __construct(
        protected readonly NavigationItemCategoryFacade $navigationItemCategoryFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData
     */
    public function createNew(): NavigationItemData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem $navigationItem
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData
     */
    public function createForEntity(NavigationItem $navigationItem): NavigationItemData
    {
        $navigationItemData = $this->createInstance();
        $navigationItemData->name = $navigationItem->getName();
        $navigationItemData->url = $navigationItem->getUrl();
        $navigationItemData->domainId = $navigationItem->getDomainId();

        $navigationItemData->categoriesByColumnNumber = $this->navigationItemCategoryFacade
            ->getSortedCategoriesIndexedByColumnNumberForNavigationItem($navigationItem);

        return $navigationItemData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData
     */
    protected function createInstance(): NavigationItemData
    {
        return new NavigationItemData();
    }
}

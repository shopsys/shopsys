<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

class NavigationItemDataFactory
{
    public function __construct(
        protected readonly NavigationItemCategoryFacade $navigationItemCategoryFacade,
    ) {
    }

    public function createNew(): NavigationItemData
    {
        return $this->createInstance();
    }

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

    protected function createInstance(): NavigationItemData
    {
        return new NavigationItemData();
    }
}

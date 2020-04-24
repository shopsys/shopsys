<?php

declare(strict_types=1);

namespace App\Model\HorizontalMenu;

class HorizontalMenuItemDataFactory
{
    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItemCategoryFacade
     */
    private $horizontalMenuItemCategoryFacade;

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemCategoryFacade $horizontalMenuItemCategoryFacade
     */
    public function __construct(
        HorizontalMenuItemCategoryFacade $horizontalMenuItemCategoryFacade
    ) {
        $this->horizontalMenuItemCategoryFacade = $horizontalMenuItemCategoryFacade;
    }

    /**
     * @return \App\Model\HorizontalMenu\HorizontalMenuItemData
     */
    public function createNew(): HorizontalMenuItemData
    {
        return new HorizontalMenuItemData();
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItem $horizontalMenuItem
     * @return \App\Model\HorizontalMenu\HorizontalMenuItemData
     */
    public function createForEntity(HorizontalMenuItem $horizontalMenuItem): HorizontalMenuItemData
    {
        $horizontalMenuItemData = new HorizontalMenuItemData();
        $horizontalMenuItemData->name = $horizontalMenuItem->getName();
        $horizontalMenuItemData->url = $horizontalMenuItem->getUrl();
        $horizontalMenuItemData->isFurniture = $horizontalMenuItem->isFurniture();
        $horizontalMenuItemData->domainId = $horizontalMenuItem->getDomainId();

        $horizontalMenuItemData->categoriesByColumnNumber = $this->horizontalMenuItemCategoryFacade
            ->getSortedCategoriesIndexedByColumnNumberForHorizontalMenuItem($horizontalMenuItem);

        return $horizontalMenuItemData;
    }
}

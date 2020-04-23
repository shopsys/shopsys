<?php

declare(strict_types=1);

namespace App\Model\HorizontalMenu;

class HorizontalMenuItemDetailFactory
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
     * @param \App\Model\HorizontalMenu\HorizontalMenuItem[] $horizontalMenuItems
     * @param int $domainId
     * @return \App\Model\HorizontalMenu\HorizontalMenuItemDetail[]
     */
    public function createDetails(array $horizontalMenuItems, int $domainId): array
    {
        $details = [];

        foreach ($horizontalMenuItems as $horizontalMenuItem) {
            $details[] = $this->createDetail($horizontalMenuItem, $domainId);
        }

        return $details;
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItem $horizontalMenuItem
     * @param int $domainId
     * @return \App\Model\HorizontalMenu\HorizontalMenuItemDetail
     */
    private function createDetail(HorizontalMenuItem $horizontalMenuItem, int $domainId): HorizontalMenuItemDetail
    {
        $categoriesByColumnNumber = $this->horizontalMenuItemCategoryFacade
            ->getSortedVisibledCategoriesIndexedByColumnNumberForHorizontalMenuItem($horizontalMenuItem, $domainId);

        $detail = new HorizontalMenuItemDetail(
            $horizontalMenuItem,
            $categoriesByColumnNumber
        );

        return $detail;
    }
}

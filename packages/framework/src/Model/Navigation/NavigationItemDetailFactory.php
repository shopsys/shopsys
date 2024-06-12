<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class NavigationItemDetailFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemCategoryFacade $navigationItemCategoryFacade
     */
    public function __construct(
        protected NavigationItemCategoryFacade $navigationItemCategoryFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem[] $navigationItems
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemDetail[]
     */
    public function createDetails(array $navigationItems, DomainConfig $domainConfig): array
    {
        $details = [];
        $categoriesIndexedByNavigationItemIdAndColumnNumber = $this->navigationItemCategoryFacade
            ->getSortedVisibleCategoriesIndexedByNavigationItemIdAndColumnNumber($navigationItems, $domainConfig);

        foreach ($navigationItems as $navigationItem) {
            if (!isset($categoriesIndexedByNavigationItemIdAndColumnNumber[$navigationItem->getId()])) {
                continue;
            }
            $details[] = new NavigationItemDetail(
                $navigationItem,
                $categoriesIndexedByNavigationItemIdAndColumnNumber[$navigationItem->getId()],
            );
        }

        return $details;
    }
}

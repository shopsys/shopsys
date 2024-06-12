<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

class NavigationItemDetail
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem $navigationItem
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[][] $categoriesByColumnNumber
     */
    public function __construct(
        protected readonly NavigationItem $navigationItem,
        protected readonly array $categoriesByColumnNumber,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem
     */
    public function getNavigationItem(): NavigationItem
    {
        return $this->navigationItem;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[][]
     */
    public function getCategoriesByColumnNumber(): array
    {
        return $this->categoriesByColumnNumber;
    }
}

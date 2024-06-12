<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

class NavigationItemDetail
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category[][]
     */
    protected array $categoryDetailsByColumnNumber;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem $navigationItem
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[][] $categoriesByColumnNumber
     */
    public function __construct(
        protected NavigationItem $navigationItem,
        array $categoriesByColumnNumber,
    ) {
        $this->categoryDetailsByColumnNumber = $categoriesByColumnNumber;
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
    public function getCategoryDetailsByColumnNumber(): array
    {
        return $this->categoryDetailsByColumnNumber;
    }
}

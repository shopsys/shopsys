<?php

declare(strict_types=1);

namespace App\Model\Navigation;

class NavigationItemDetail
{
    /**
     * @var \App\Model\Category\Category[][]
     */
    private array $categoryDetailsByColumnNumber;

    /**
     * @param \App\Model\Navigation\NavigationItem $navigationItem
     * @param \App\Model\Category\Category[][] $categoriesByColumnNumber
     */
    public function __construct(
        private NavigationItem $navigationItem,
        array $categoriesByColumnNumber,
    ) {
        $this->categoryDetailsByColumnNumber = $categoriesByColumnNumber;
    }

    /**
     * @return \App\Model\Navigation\NavigationItem
     */
    public function getNavigationItem(): NavigationItem
    {
        return $this->navigationItem;
    }

    /**
     * @return \App\Model\Category\Category[][]
     */
    public function getCategoryDetailsByColumnNumber(): array
    {
        return $this->categoryDetailsByColumnNumber;
    }
}

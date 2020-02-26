<?php

declare(strict_types=1);

namespace App\Model\HorizontalMenu;

class HorizontalMenuItemDetail
{
    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItem
     */
    private $horizontalMenuItem;

    /**
     * @var \App\Model\Category\Category[][]
     */
    private $categoryDetailsByColumnNumber;

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItem $horizontalMenuItem
     * @param \App\Model\Category\Category[][] $categoriesByColumnNumber
     */
    public function __construct(
        HorizontalMenuItem $horizontalMenuItem,
        array $categoriesByColumnNumber
    ) {
        $this->horizontalMenuItem = $horizontalMenuItem;
        $this->categoryDetailsByColumnNumber = $categoriesByColumnNumber;
    }

    /**
     * @return \App\Model\HorizontalMenu\HorizontalMenuItem
     */
    public function getHorizontalMenuItem(): HorizontalMenuItem
    {
        return $this->horizontalMenuItem;
    }

    /**
     * @return \App\Model\Category\Category[][]
     */
    public function getCategoryDetailsByColumnNumber(): array
    {
        return $this->categoryDetailsByColumnNumber;
    }
}

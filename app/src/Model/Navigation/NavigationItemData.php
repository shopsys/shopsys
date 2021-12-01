<?php

declare(strict_types=1);

namespace App\Model\Navigation;

class NavigationItemData
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $url;

    /**
     * @var \App\Model\Category\Category[][]
     */
    public $categoriesByColumnNumber = [];

    /**
     * @var int
     */
    public $domainId;
}

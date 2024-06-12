<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

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
     * @var \Shopsys\FrameworkBundle\Model\Category\Category[][]
     */
    public $categoriesByColumnNumber = [];

    /**
     * @var int
     */
    public $domainId;
}

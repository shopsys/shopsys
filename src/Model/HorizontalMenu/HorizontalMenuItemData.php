<?php

declare(strict_types=1);

namespace App\Model\HorizontalMenu;

class HorizontalMenuItemData
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
     * @var bool|null
     */
    public $isFurniture;

    /**
     * @var \App\Model\Category\Category[][]
     */
    public $categoriesByColumnNumber = [];

    /**
     * @var int
     */
    public $domainId;
}

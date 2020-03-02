<?php

declare(strict_types=1);

namespace App\Model\Category;

use Shopsys\FrameworkBundle\Model\Category\CategoryData as BaseCategoryData;

/**
 * @property \App\Model\Category\Category|null $parent
 */
class CategoryData extends BaseCategoryData
{
    /**
     * @var string|null
     */
    public $akeneoCode;

    /**
     * @var string|null
     */
    public $svgIcon;

    /**
     * @var string[]|null[]
     */
    public $shortDescription;

    public function __construct()
    {
        parent::__construct();
        $this->shortDescription = [];
    }
}

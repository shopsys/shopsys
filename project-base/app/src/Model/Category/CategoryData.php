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
     * @var \App\Model\Product\Parameter\Parameter[]
     */
    public $parametersCollapsed;

    /**
     * @var string[]|null[]
     */
    public $shortDescription;

    /**
     * @var int[]|null[]
     */
    public $parametersPosition;

    /**
     * @var \App\Model\Category\Category[]
     */
    public $linkedCategories;

    public function __construct()
    {
        parent::__construct();

        $this->shortDescription = [];
        $this->parametersPosition = [];
        $this->linkedCategories = [];
    }
}

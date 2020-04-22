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
     * @var \App\Model\Product\Parameter\Parameter[]
     */
    public $parameters;

    /**
     * @var \App\Model\Product\Parameter\Parameter[]
     */
    public $parametersCollapsed;

    /**
     * @var string[]|null[]
     */
    public $shortDescription;

    /**
     * @var string[]|null[]
     */
    public $productSeriesListTitle;

    /**
     * @var string[]|null[]
     */
    public $productSeriesListDescription;

    /**
     * @var string[]|null[]
     */
    public $productSeriesListLink;

    /**
     * @var \App\Model\Product\Series\ProductSeries[]
     */
    public $categoryProductSeries;

    public function __construct()
    {
        parent::__construct();
        $this->shortDescription = [];
        $this->productSeriesListTitle = [];
        $this->productSeriesListDescription = [];
        $this->productSeriesListLink = [];
        $this->categoryProductSeries = [];
    }
}

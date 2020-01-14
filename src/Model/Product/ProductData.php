<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;

/**
 * @property \App\Model\Category\Category[][] $categoriesByDomainId
 * @property \App\Model\Product\Brand\Brand|null $brand
 * @property \App\Model\Product\Product[] $accessories
 * @property \App\Model\Product\Product[] $variants
 */
class ProductData extends BaseProductData
{
    /**
     * @var string[]|null[]
     */
    public $shortDescriptionUsp1;

    /**
     * @var string[]|null[]
     */
    public $shortDescriptionUsp2;

    /**
     * @var string[]|null[]
     */
    public $shortDescriptionUsp3;

    /**
     * @var string[]|null[]
     */
    public $shortDescriptionUsp4;

    /**
     * @var string[]|null[]
     */
    public $shortDescriptionUsp5;

    /**
     * @var string[]|null[]
     */
    public $namePrefix;

    /**
     * @var string[]|null[]
     */
    public $nameSufix;

    public function __construct()
    {
        parent::__construct();

        $this->shortDescriptionUsp1 = [];
        $this->shortDescriptionUsp2 = [];
        $this->shortDescriptionUsp3 = [];
        $this->shortDescriptionUsp4 = [];
        $this->shortDescriptionUsp5 = [];
        $this->namePrefix = [];
        $this->nameSufix = [];
    }
}

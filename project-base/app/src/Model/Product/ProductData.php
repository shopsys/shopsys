<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;

/**
 * @property \App\Model\Category\Category[][] $categoriesByDomainId
 * @property \App\Model\Product\Brand\Brand|null $brand
 * @property \App\Model\Product\Product[] $variants
 * @property \App\Model\Product\Product[] $accessories
 * @property \App\Model\Product\Unit\Unit|null $unit
 * @property \App\Model\Product\Flag\Flag[][]|null[][] $flagsByDomainId
 */
class ProductData extends BaseProductData
{
    /**
     * @var string[]|null[]
     */
    public $namePrefix;

    /**
     * @var string[]|null[]
     */
    public $nameSufix;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Stock\ProductStockData[]
     */
    public $stockProductData;

    /**
     * @var int|null
     */
    public ?int $weight;

    /**
     * @var \App\Model\Product\Product[]
     */
    public array $relatedProducts;

    /**
     * @var \App\Model\ProductVideo\ProductVideoData[]
     */
    public $productVideosData;

    public function __construct()
    {
        parent::__construct();

        $this->namePrefix = [];
        $this->nameSufix = [];
        $this->stockProductData = [];
        $this->weight = null;
        $this->relatedProducts = [];
        $this->productVideosData = [];
    }
}

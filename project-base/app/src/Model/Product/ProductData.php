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

    /**
     * @var \Shopsys\FrameworkBundle\Model\Stock\ProductStockData[]
     */
    public $stockProductData;

    /**
     * @var bool
     */
    public $downloadAssemblyInstructionFiles = false;

    /**
     * @var bool
     */
    public $downloadProductTypePlanFiles = false;

    /**
     * @var string[]|null[]
     */
    public $assemblyInstructionCode;

    /**
     * @var string[]|null[]
     */
    public $productTypePlanCode;

    /**
     * @var string[]|null[]
     */
    public $assemblyInstructionFileUrl;

    /**
     * @var string[]|null[]
     */
    public $productTypePlanFileUrl;

    /**
     * @var bool
     */
    public $preorder;

    /**
     * @var bool[]
     */
    public $saleExclusion;

    /**
     * @var int|null
     */
    public $vendorDeliveryDate;

    /**
     * @var \App\Model\Product\Flag\Flag[][]|null[][]
     * @phpstan-ignore-next-line Overridden property type
     */
    public $flags;

    /**
     * @var bool[]|null[]
     */
    public $domainHidden;

    /**
     * @var int[]|null[]
     */
    public $domainOrderingPriority;

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

        $this->shortDescriptionUsp1 = [];
        $this->shortDescriptionUsp2 = [];
        $this->shortDescriptionUsp3 = [];
        $this->shortDescriptionUsp4 = [];
        $this->shortDescriptionUsp5 = [];
        $this->namePrefix = [];
        $this->nameSufix = [];
        $this->stockProductData = [];
        $this->assemblyInstructionCode = [];
        $this->productTypePlanCode = [];
        $this->assemblyInstructionFileUrl = [];
        $this->productTypePlanFileUrl = [];
        $this->preorder = false;
        $this->saleExclusion = [];
        $this->flags = [];
        $this->domainHidden = [];
        $this->domainOrderingPriority = [];
        $this->weight = null;
        $this->relatedProducts = [];
        $this->productVideosData = [];
    }
}

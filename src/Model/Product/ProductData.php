<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;

/**
 * @property \App\Model\Category\Category[][] $categoriesByDomainId
 * @property \App\Model\Product\Brand\Brand|null $brand
 * @property \App\Model\Product\Product[] $variants
 * @property \App\Model\Product\Flag\Flag[][] $flags
 * @property \App\Model\Product\Product[] $accessories
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
     * @var \App\Model\Stock\ProductStockData[]
     */
    public $stockProductData;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money[]|null[]
     */
    public $lowPriceWithVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money[]|null[]
     */
    public $highPriceWithVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money[]|null[]
     */
    public $lowPriceWithoutVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money[]|null[]
     */
    public $highPriceWithoutVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money[]|null[]
     */
    public $sellingPriceWithVat;

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
     * @var \App\Model\Product\Type\ProductType[]|null[]
     */
    public $productType;

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
     */
    public $flags;

    /**
     * @var bool[]|null[]
     */
    public $mountingState;

    /**
     * @var string[]|null[]
     */
    public $embeddedAccessories;

    /**
     * @var string[]|null[]
     */
    public $packageNotIncluded;

    /**
     * @var int[]|null[]
     */
    public $packagingUnit;

    /**
     * @var int[]|null[]
     */
    public $countPackages;

    /**
     * @var float[]|null[]
     */
    public $totalPackageWeight;

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
        $this->lowPriceWithVat = [];
        $this->highPriceWithVat = [];
        $this->lowPriceWithoutVat = [];
        $this->highPriceWithoutVat = [];
        $this->assemblyInstructionCode = [];
        $this->productTypePlanCode = [];
        $this->assemblyInstructionFileUrl = [];
        $this->productTypePlanFileUrl = [];
        $this->productType = [];
        $this->preorder = false;
        $this->saleExclusion = [];
        $this->flags = [];
        $this->packagingUnit = [];
        $this->countPackages = [];
        $this->totalPackageWeight = [];
    }
}

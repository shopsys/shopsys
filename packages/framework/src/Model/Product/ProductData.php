<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

class ProductData
{
    /**
     * @var string[]|null[]
     */
    public $name;

    /**
     * @var string|null
     */
    public $catnum;

    /**
     * @var string|null
     */
    public $partno;

    /**
     * @var string|null
     */
    public $ean;

    /**
     * @var \DateTime|null
     */
    public $sellingFrom;

    /**
     * @var \DateTime|null
     */
    public $sellingTo;

    /**
     * @var bool
     */
    public $sellingDenied;

    /**
     * @var bool
     */
    public $hidden;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\Unit|null
     */
    public $unit;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[][]|null[][]
     */
    public $flagsByDomainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category[][]
     */
    public $categoriesByDomainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|null
     */
    public $brand;

    /**
     * @var string[]|null[]
     */
    public $variantAlias;

    /**
     * @var int[]|null[]
     */
    public $orderingPriorityByDomainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[]
     */
    public $parameters;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $images;

    /**
     * @var string[]|null[]
     */
    public $seoTitles;

    /**
     * @var string[]|null[]
     */
    public $seoMetaDescriptions;

    /**
     * @var string[]|null[]
     */
    public $descriptions;

    /**
     * @var string[]|null[]
     */
    public $shortDescriptions;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData
     */
    public $urls;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public $accessories;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public $variants;

    /**
     * @var string[]|null[]
     */
    public $seoH1s;

    /**
     * @var array<string, mixed>
     */
    public $pluginData;

    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var string[]|null[]
     */
    public $shortDescriptionUsp1ByDomainId;

    /**
     * @var string[]|null[]
     */
    public $shortDescriptionUsp2ByDomainId;

    /**
     * @var string[]|null[]
     */
    public $shortDescriptionUsp3ByDomainId;

    /**
     * @var string[]|null[]
     */
    public $shortDescriptionUsp4ByDomainId;

    /**
     * @var string[]|null[]
     */
    public $shortDescriptionUsp5ByDomainId;

    /**
     * @var bool[]
     */
    public $saleExclusion;

    /**
     * @var bool[]|null[]
     */
    public $domainHidden;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Stock\ProductStockData[]
     */
    public $productStockData;

    /**
     * @var int|null
     */
    public $weight;

    /**
     * @var array<int, \Shopsys\FrameworkBundle\Model\Product\ProductInputPriceData>
     */
    public $productInputPricesByDomain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData
     */
    public $files;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public $excludedTransports;

    public function __construct()
    {
        $this->name = [];
        $this->sellingDenied = false;
        $this->hidden = false;
        $this->flagsByDomainId = [];
        $this->productStockData = [];
        $this->categoriesByDomainId = [];
        $this->variantAlias = [];
        $this->parameters = [];
        $this->productInputPricesByDomain = [];
        $this->seoTitles = [];
        $this->seoMetaDescriptions = [];
        $this->descriptions = [];
        $this->shortDescriptions = [];
        $this->urls = new UrlListData();
        $this->accessories = [];
        $this->variants = [];
        $this->seoH1s = [];
        $this->pluginData = [];
        $this->orderingPriorityByDomainId = [];
        $this->shortDescriptionUsp1ByDomainId = [];
        $this->shortDescriptionUsp2ByDomainId = [];
        $this->shortDescriptionUsp3ByDomainId = [];
        $this->shortDescriptionUsp4ByDomainId = [];
        $this->shortDescriptionUsp5ByDomainId = [];
        $this->saleExclusion = [];
        $this->domainHidden = [];
        $this->excludedTransports = [];
    }
}

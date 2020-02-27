<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

class ProductSeriesData
{
    /**
     * @var string[]|null[]
     */
    public $name;

    /**
     * @var string[]|null[]
     */
    public $description;

    /**
     * @var bool[]
     */
    public $hidden;

    /**
     * @var string[]|null[]
     */
    public $seoTitle;

    /**
     * @var string[]|null[]
     */
    public $seoMetaDescription;

    /**
     * @var string[]|null[]
     */
    public $seoH1;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData
     */
    public $url;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $images;

    /**
     * @var \App\Model\Product\Series\Category\ProductSeriesCategory[]
     */
    public $productSeriesCategories;

    public function __construct()
    {
        //by language
        $this->name = [];
        $this->description = [];

        //by domain
        $this->hidden = [];
        $this->seoTitle = [];
        $this->seoMetaDescription = [];
        $this->seoH1 = [];

        $this->productSeriesCategories = [];

        $this->url = new UrlListData();
        $this->images = new ImageUploadData();
    }
}

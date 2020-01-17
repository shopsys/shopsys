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
    public $names;

    /**
     * @var string[]|null[]
     */
    public $descriptions;

    /**
     * @var bool[]
     */
    public $hidden;

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
    public $seoH1s;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData
     */
    public $urls;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $images;

    public function __construct()
    {
        //by language
        $this->names = [];
        $this->descriptions = [];

        //by domain
        $this->hidden = [];
        $this->seoTitles = [];
        $this->seoMetaDescriptions = [];
        $this->seoH1s = [];

        $this->urls = new UrlListData();
        $this->images = new ImageUploadData();
    }
}

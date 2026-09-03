<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

class BrandData
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $image;

    /**
     * @var string[]|null[]
     */
    public $descriptions;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData
     */
    public $urls;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoAttributesData[]
     */
    public $seo;

    /**
     * @var string|null
     */
    public $uuid;

    public function __construct()
    {
        $this->name = '';
        $this->descriptions = [];
        $this->urls = new UrlListData();
        $this->seo = [];
    }
}

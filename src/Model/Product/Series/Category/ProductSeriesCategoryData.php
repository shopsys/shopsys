<?php

declare(strict_types=1);


namespace App\Model\Product\Series\Category;


use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

class ProductSeriesCategoryData
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

    public function __construct()
    {
        //by language
        $this->name = [];
        $this->description = [];

        //by domain
        $this->seoTitle = [];
        $this->seoMetaDescription = [];
        $this->seoH1 = [];

        $this->url = new UrlListData();
    }
}
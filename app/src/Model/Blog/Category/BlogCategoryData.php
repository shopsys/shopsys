<?php

declare(strict_types=1);

namespace App\Model\Blog\Category;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

class BlogCategoryData
{
    /**
     * @var string[]|null[]
     */
    public $names;

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
     * @var string[]|null[]
     */
    public $descriptions;

    /**
     * @var \App\Model\Blog\Category\BlogCategory|null
     */
    public $parent;

    /**
     * @var bool[]
     */
    public $enabled;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData
     */
    public $urls;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $image;

    /**
     * @var string|null
     */
    public ?string $uuid = null;

    public function __construct()
    {
        $this->names = [];
        $this->seoTitles = [];
        $this->seoMetaDescriptions = [];
        $this->seoH1s = [];
        $this->parent = null;
        $this->descriptions = [];
        $this->enabled = [];
        $this->urls = new UrlListData();
    }
}

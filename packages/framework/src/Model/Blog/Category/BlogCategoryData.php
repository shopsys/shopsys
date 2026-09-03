<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

class BlogCategoryData
{
    /**
     * @var string[]|null[]
     */
    public $names;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoAttributesData[]
     */
    public $seo;

    /**
     * @var string[]|null[]
     */
    public $descriptions;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory|null
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
    public $uuid;

    public function __construct()
    {
        $this->names = [];
        $this->seo = [];
        $this->parent = null;
        $this->descriptions = [];
        $this->enabled = [];
        $this->urls = new UrlListData();
    }
}

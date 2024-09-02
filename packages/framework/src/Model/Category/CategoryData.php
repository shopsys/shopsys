<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

class CategoryData
{
    /**
     * @var string[]|null[]
     */
    public $name;

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
     * @var \Shopsys\FrameworkBundle\Model\Category\Category|null
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
     * @var array<string, mixed>
     */
    public $pluginData;

    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var int[]|null[]
     */
    public $parametersPosition;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public $parametersCollapsed;

    public function __construct()
    {
        $this->name = [];
        $this->seoTitles = [];
        $this->seoMetaDescriptions = [];
        $this->seoH1s = [];
        $this->descriptions = [];
        $this->enabled = [];
        $this->urls = new UrlListData();
        $this->pluginData = [];
        $this->parametersPosition = [];
        $this->parametersCollapsed = [];
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Symfony\Component\Clock\DatePoint;

class BlogArticleData
{
    /**
     * @var string[]|null[]
     */
    public $names;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[][]
     */
    public $blogCategoriesByDomainId;

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
     * @var \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public $categories;

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
     * @var bool
     */
    public $visibleOnHomepage;

    /**
     * @var \DateTimeImmutable[]|null[]
     */
    public $publishDates;

    /**
     * @var string[]
     */
    public $statuses;

    /**
     * @var \DateTimeImmutable|null
     */
    public $createdAt;

    /**
     * @var string[]|null[]
     */
    public $perexes;

    /**
     * @var string|null
     */
    public $uuid;

    public function __construct()
    {
        $this->names = [];
        $this->blogCategoriesByDomainId = [];
        $this->seoTitles = [];
        $this->seoMetaDescriptions = [];
        $this->seoH1s = [];
        $this->descriptions = [];
        $this->categories = [];
        $this->enabled = [];
        $this->urls = new UrlListData();
        $this->visibleOnHomepage = true;
        $this->perexes = [];
        $this->createdAt = new DatePoint();
        $this->publishDates = [];
        $this->statuses = [];
    }
}

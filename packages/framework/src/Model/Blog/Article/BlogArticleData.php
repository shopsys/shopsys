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
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoAttributesData[]
     */
    public $seo;

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

    /**
     * @var \Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthor|null
     */
    public $blogArticleAuthor;

    public function __construct()
    {
        $this->names = [];
        $this->blogCategoriesByDomainId = [];
        $this->seo = [];
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

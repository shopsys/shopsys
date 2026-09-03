<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Symfony\Component\Clock\DatePoint;

class ArticleData
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $text;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoAttributesData
     */
    public $seo;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData
     */
    public $urls;

    /**
     * @var string|null
     */
    public $placement;

    /**
     * @var bool
     */
    public $hidden;

    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var \DateTimeImmutable|null
     */
    public $createdAt;

    /**
     * @var bool
     */
    public $external = false;

    /**
     * @var string
     */
    public $type = Article::TYPE_SITE;

    /**
     * @var string|null
     */
    public $url;

    public function __construct()
    {
        $this->urls = new UrlListData();
        $this->hidden = false;
        $this->createdAt = new DatePoint();
    }
}

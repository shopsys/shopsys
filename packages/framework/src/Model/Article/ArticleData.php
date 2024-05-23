<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use DateTime;
use Shopsys\Administration\Component\AdminSortableInterface;
use Shopsys\Administration\Component\Security\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;

class ArticleData implements AdminIdentifierInterface, AdminSortableInterface
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $text;

    /**
     * @var string|null
     */
    public $seoTitle;

    /**
     * @var string|null
     */
    public $seoMetaDescription;

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
    public $seoH1;

    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var \DateTime|null
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

    /**
     * @var int|null
     */
    public $position;

    public function __construct()
    {
        $this->urls = new UrlListData();
        $this->hidden = false;
        $this->createdAt = new DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }
}

<?php

declare(strict_types=1);

namespace App\Model\Article;

use DateTime;
use Shopsys\FrameworkBundle\Model\Article\ArticleData as BaseArticleData;

class ArticleData extends BaseArticleData
{
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
     * @var string
     */
    public $url;

    public function __construct()
    {
        parent::__construct();

        $this->createdAt = new DateTime();
    }
}

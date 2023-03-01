<?php

declare(strict_types=1);

namespace App\Model\Article;

use Shopsys\FrameworkBundle\Model\Article\ArticleData as BaseArticleData;

class ArticleData extends BaseArticleData
{
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
    public ?string $url = null;
}

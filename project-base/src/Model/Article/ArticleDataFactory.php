<?php

declare(strict_types=1);

namespace App\Model\Article;

use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleData as BaseArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory as BaseArticleDataFactory;

/**
 * @method \App\Model\Article\ArticleData create()
 * @method \App\Model\Article\ArticleData createFromArticle(\App\Model\Article\Article $article)
 */
class ArticleDataFactory extends BaseArticleDataFactory
{
    /**
     * @return \App\Model\Article\ArticleData
     */
    protected function createInstance(): BaseArticleData
    {
        return new ArticleData();
    }

    /**
     * @param \App\Model\Article\ArticleData $articleData
     * @param \App\Model\Article\Article $article
     */
    protected function fillFromArticle(BaseArticleData $articleData, Article $article): void
    {
        parent::fillFromArticle($articleData, $article);
    }
}

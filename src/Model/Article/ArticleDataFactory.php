<?php

declare(strict_types=1);

namespace App\Model\Article;

use DateTime;
use Shopsys\FrameworkBundle\Model\Article\Article as BaseArticle;
use Shopsys\FrameworkBundle\Model\Article\ArticleData as BaseArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory as BaseArticleDataFactory;

class ArticleDataFactory extends BaseArticleDataFactory
{
    /**
     * @param \App\Model\Article\Article $article
     * @return \App\Model\Article\ArticleData
     */
    public function createFromArticle(BaseArticle $article): BaseArticleData
    {
        $articleData = new ArticleData();
        $this->fillFromArticle($articleData, $article);

        return $articleData;
    }

    /**
     * @return \App\Model\Article\ArticleData
     */
    public function create(): BaseArticleData
    {
        $articleData = new ArticleData();
        $this->fillNew($articleData);

        return $articleData;
    }

    /**
     * @param \App\Model\Article\ArticleData $articleData
     * @param \App\Model\Article\Article $article
     */
    protected function fillFromArticle(BaseArticleData $articleData, BaseArticle $article)
    {
        parent::fillFromArticle($articleData, $article);

        $articleData->createdAt = $article->getCreatedAt() ?? new DateTime();
        $articleData->external = $article->isExternal();
        $articleData->type = $article->getType();
        $articleData->url = $article->getUrl() ?? Article::EMPTY_STRING;


    }
}

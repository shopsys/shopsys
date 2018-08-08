<?php

namespace Shopsys\FrameworkBundle\Model\Article;

interface ArticleDataFactoryInterface
{
    public function create(): ArticleData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function createFromArticle(Article $article): ArticleData;
}

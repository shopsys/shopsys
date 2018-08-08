<?php

namespace Shopsys\FrameworkBundle\Model\Article;

interface ArticleDataFactoryInterface
{
    public function create(): ArticleData;

    public function createFromArticle(Article $article): ArticleData;
}

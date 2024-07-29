<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

interface ArticleDataFactoryInterface
{
    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Article\ArticleData
     */
    public function create(int $domainId): ArticleData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\Article $article
     * @return \Shopsys\FrameworkBundle\Model\Article\ArticleData
     */
    public function createFromArticle(Article $article): ArticleData;
}

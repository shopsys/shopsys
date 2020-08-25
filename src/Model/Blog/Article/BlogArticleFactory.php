<?php

declare(strict_types=1);

namespace App\Model\Blog\Article;

class BlogArticleFactory
{
    /**
     * @param \App\Model\Blog\Article\BlogArticleData $data
     * @return \App\Model\Blog\Article\BlogArticle
     */
    public function create(BlogArticleData $data): BlogArticle
    {
        return new BlogArticle($data);
    }
}

<?php

declare(strict_types=1);

namespace App\Model\Blog\Article;

use App\Model\Blog\Category\BlogCategory;

class BlogArticleBlogCategoryDomainFactory
{
    /**
     * @param \App\Model\Blog\Article\BlogArticle $blogArticle
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $domainId
     * @return \App\Model\Blog\Article\BlogArticleBlogCategoryDomain
     */
    public function create(
        BlogArticle $blogArticle,
        BlogCategory $blogCategory,
        int $domainId
    ): BlogArticleBlogCategoryDomain {
        return new BlogArticleBlogCategoryDomain($blogArticle, $blogCategory, $domainId);
    }
}

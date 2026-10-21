<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

class BlogArticlesByBlogCategoryBatchLoadDataFactory
{
    public function create(
        BlogCategory $blogCategory,
        int $offset,
        int $limit,
        bool $onlyVisibleOnHomepage,
    ): BlogArticlesByBlogCategoryBatchLoadData {
        return $this->createInstance($blogCategory, $offset, $limit, $onlyVisibleOnHomepage);
    }

    protected function createInstance(
        BlogCategory $blogCategory,
        int $offset,
        int $limit,
        bool $onlyVisibleOnHomepage,
    ): BlogArticlesByBlogCategoryBatchLoadData {
        return new BlogArticlesByBlogCategoryBatchLoadData($blogCategory, $offset, $limit, $onlyVisibleOnHomepage);
    }
}

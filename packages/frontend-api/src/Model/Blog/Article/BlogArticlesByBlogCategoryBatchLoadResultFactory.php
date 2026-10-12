<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

class BlogArticlesByBlogCategoryBatchLoadResultFactory
{
    /**
     * @param array<int, array<string, mixed>> $blogArticles
     */
    public function create(array $blogArticles, int $totalCount): BlogArticlesByBlogCategoryBatchLoadResult
    {
        return $this->createInstance($blogArticles, $totalCount);
    }

    /**
     * @param array<int, array<string, mixed>> $blogArticles
     */
    protected function createInstance(array $blogArticles, int $totalCount): BlogArticlesByBlogCategoryBatchLoadResult
    {
        return new BlogArticlesByBlogCategoryBatchLoadResult($blogArticles, $totalCount);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

class BlogArticlesByBlogCategoryBatchLoadResult
{
    /**
     * @param array<int, array<string, mixed>> $blogArticles
     */
    public function __construct(
        public readonly array $blogArticles,
        public readonly int $totalCount,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

class BlogArticlesByBlogCategoryBatchLoadResult
{
    /**
     * @param array<int, array<string, mixed>> $blogArticles
     */
    public function __construct(
        protected readonly array $blogArticles,
        protected readonly int $totalCount,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getBlogArticles(): array
    {
        return $this->blogArticles;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
}

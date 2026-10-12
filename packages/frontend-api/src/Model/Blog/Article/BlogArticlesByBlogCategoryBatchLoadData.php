<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

class BlogArticlesByBlogCategoryBatchLoadData
{
    public function __construct(
        protected readonly BlogCategory $blogCategory,
        protected readonly int $offset,
        protected readonly int $limit,
        protected readonly bool $onlyVisibleOnHomepage,
    ) {
    }

    public function getBlogCategory(): BlogCategory
    {
        return $this->blogCategory;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function isOnlyVisibleOnHomepage(): bool
    {
        return $this->onlyVisibleOnHomepage;
    }
}

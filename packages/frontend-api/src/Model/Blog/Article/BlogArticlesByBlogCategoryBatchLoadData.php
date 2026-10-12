<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

class BlogArticlesByBlogCategoryBatchLoadData
{
    public function __construct(
        public readonly BlogCategory $blogCategory,
        public readonly int $offset,
        public readonly int $limit,
        public readonly bool $onlyVisibleOnHomepage,
    ) {
    }
}

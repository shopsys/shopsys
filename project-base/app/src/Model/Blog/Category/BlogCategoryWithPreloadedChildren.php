<?php

declare(strict_types=1);

namespace App\Model\Blog\Category;

class BlogCategoryWithPreloadedChildren
{
    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param \App\Model\Blog\Category\BlogCategoryWithPreloadedChildren[] $children
     */
    public function __construct(
        private BlogCategory $blogCategory,
        private array $children,
    ) {
    }

    /**
     * @return \App\Model\Blog\Category\BlogCategory
     */
    public function getBlogCategory(): BlogCategory
    {
        return $this->blogCategory;
    }

    /**
     * @return \App\Model\Blog\Category\BlogCategoryWithPreloadedChildren[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}

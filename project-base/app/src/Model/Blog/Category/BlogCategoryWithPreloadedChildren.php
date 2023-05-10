<?php

declare(strict_types=1);

namespace App\Model\Blog\Category;

class BlogCategoryWithPreloadedChildren
{
    /**
     * @var \App\Model\Blog\Category\BlogCategory
     */
    private $blogCategory;

    /**
     * @var \App\Model\Blog\Category\BlogCategoryWithPreloadedChildren[]
     */
    private $children;

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param \App\Model\Blog\Category\BlogCategoryWithPreloadedChildren[] $children
     */
    public function __construct(
        BlogCategory $blogCategory,
        array $children
    ) {
        $this->blogCategory = $blogCategory;
        $this->children = $children;
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

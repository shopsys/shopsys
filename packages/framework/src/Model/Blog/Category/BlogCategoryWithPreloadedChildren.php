<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

class BlogCategoryWithPreloadedChildren
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryWithPreloadedChildren[] $children
     */
    public function __construct(
        protected readonly BlogCategory $blogCategory,
        protected readonly array $children,
    ) {
    }

    public function getBlogCategory(): BlogCategory
    {
        return $this->blogCategory;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryWithPreloadedChildren[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}

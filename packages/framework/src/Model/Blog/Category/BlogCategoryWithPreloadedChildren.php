<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

class BlogCategoryWithPreloadedChildren
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryWithPreloadedChildren[] $children
     */
    public function __construct(
        protected readonly BlogCategory $blogCategory,
        protected readonly array $children,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
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

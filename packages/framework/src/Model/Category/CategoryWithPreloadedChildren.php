<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

/**
 * In the administration, the whole category tree is rendered at once.
 * Purpose of this class is to prevent multiple SQL queries that would be triggered
 * during the rendering of the tree using standard Doctrine entities
 * when accessing "category.children" (i.e. Category::getChildren()) in the template.
 *
 * @see \Shopsys\FrameworkBundle\Model\Category\Category
 */
class CategoryWithPreloadedChildren
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildren[] $children
     */
    public function __construct(
        protected readonly Category $category,
        protected readonly array $children,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildren[]
     */
    public function getChildren()
    {
        return $this->children;
    }
}

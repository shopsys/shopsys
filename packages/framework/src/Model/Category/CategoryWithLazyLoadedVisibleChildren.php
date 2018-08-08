<?php

namespace Shopsys\FrameworkBundle\Model\Category;

use Closure;

/**
 * The class encapsulates Category along with it's visible children
 * that are lazy loaded to prevent unnecessary SQL queries.
 * It is used for rendering front-end category tree.
 * @see \Shopsys\FrameworkBundle\Model\Category\Category
 */
class CategoryWithLazyLoadedVisibleChildren
{
    /**
     * @var \Closure
     */
    private $lazyLoadChildrenCallback;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     */
    private $category;

    /**
     * @var bool
     */
    private $hasChildren;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildren[]|null
     */
    private $children;

    public function __construct(
        Closure $lazyLoadChildrenCallback,
        Category $category,
        bool $hasChildren
    ) {
        $this->lazyLoadChildrenCallback = $lazyLoadChildrenCallback;
        $this->category = $category;
        $this->hasChildren = $hasChildren;
    }

    public function getCategory(): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        return $this->category;
    }

    public function hasChildren(): bool
    {
        return $this->hasChildren;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildren[]
     */
    public function getChildren(): array
    {
        if ($this->children === null) {
            $this->children = call_user_func($this->lazyLoadChildrenCallback);
        }

        return $this->children;
    }
}

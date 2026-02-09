<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Closure;

/**
 * The class encapsulates Category along with it's visible children
 * that are lazy loaded to prevent unnecessary SQL queries.
 * It is used for rendering front-end category tree.
 *
 * @see \Shopsys\FrameworkBundle\Model\Category\Category
 */
class CategoryWithLazyLoadedVisibleChildren
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildren[]|null
     */
    protected ?array $children = null;

    public function __construct(
        protected readonly Closure $lazyLoadChildrenCallback,
        protected readonly Category $category,
        protected bool $hasChildren,
    ) {
    }

    public function getCategory(): Category
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

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Category\Category;

class NavigationItemCategoryFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem $navigationItem
     * @param int $columnNumber
     * @param int $position
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemCategory
     */
    public function create(
        NavigationItem $navigationItem,
        int $columnNumber,
        int $position,
        Category $category,
    ): NavigationItemCategory {
        $entityClassName = $this->entityNameResolver->resolve(NavigationItemCategory::class);

        return new $entityClassName(
            $navigationItem,
            $columnNumber,
            $position,
            $category,
        );
    }
}

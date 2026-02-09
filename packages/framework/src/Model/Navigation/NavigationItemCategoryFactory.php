<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Category\Category;

class NavigationItemCategoryFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

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

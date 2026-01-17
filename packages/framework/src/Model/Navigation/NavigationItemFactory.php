<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class NavigationItemFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(NavigationItemData $navigationItemData): NavigationItem
    {
        $entityClassName = $this->entityNameResolver->resolve(NavigationItem::class);

        return new $entityClassName($navigationItemData);
    }
}

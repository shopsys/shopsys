<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class NavigationItemFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData $navigationItemData
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem
     */
    public function create(NavigationItemData $navigationItemData): NavigationItem
    {
        $entityClassName = $this->entityNameResolver->resolve(NavigationItem::class);

        return new $entityClassName($navigationItemData);
    }
}

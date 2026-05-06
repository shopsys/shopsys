<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AdministratorPinnedMenuItemFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(Administrator $administrator, string $routeName, int $position): AdministratorPinnedMenuItem
    {
        $entityClassName = $this->entityNameResolver->resolve(AdministratorPinnedMenuItem::class);

        return new $entityClassName($administrator, $routeName, $position);
    }
}

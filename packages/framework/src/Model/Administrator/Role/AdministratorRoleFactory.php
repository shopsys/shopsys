<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Role;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AdministratorRoleFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(AdministratorRoleData $administratorRoleData): AdministratorRole
    {
        $entityClassName = $this->entityNameResolver->resolve(AdministratorRole::class);

        return new $entityClassName($administratorRoleData);
    }
}

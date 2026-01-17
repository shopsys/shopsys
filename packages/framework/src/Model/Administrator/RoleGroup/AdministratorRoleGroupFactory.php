<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AdministratorRoleGroupFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(AdministratorRoleGroupData $data): AdministratorRoleGroup
    {
        $entityClassName = $this->entityNameResolver->resolve(AdministratorRoleGroup::class);

        return new $entityClassName($data);
    }
}

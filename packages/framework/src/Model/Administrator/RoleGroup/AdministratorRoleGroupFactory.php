<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AdministratorRoleGroupFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupData $data
     * @return \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup
     */
    public function create(AdministratorRoleGroupData $data): AdministratorRoleGroup
    {
        $entityClassName = $this->entityNameResolver->resolve(AdministratorRoleGroup::class);

        return new $entityClassName($data);
    }
}

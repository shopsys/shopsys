<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Role;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AdministratorRoleFactory implements AdministratorRoleFactoryInterface
{
    protected EntityNameResolver $entityNameResolver;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(EntityNameResolver $entityNameResolver)
    {
        $this->entityNameResolver = $entityNameResolver;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleData $administratorRoleData
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole
     */
    public function create(AdministratorRoleData $administratorRoleData): AdministratorRole
    {
        $classData = $this->entityNameResolver->resolve(AdministratorRole::class);

        return new $classData($administratorRoleData);
    }
}

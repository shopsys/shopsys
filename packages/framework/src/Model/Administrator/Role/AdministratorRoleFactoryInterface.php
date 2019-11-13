<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Role;

interface AdministratorRoleFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleData $administratorRoleData
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole
     */
    public function create(AdministratorRoleData $administratorRoleData): AdministratorRole;
}

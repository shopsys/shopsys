<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Role;

interface AdministratorRoleDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleData
     */
    public function create(): AdministratorRoleData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole $administratorRole
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleData
     */
    public function createFromAdministratorRole(AdministratorRole $administratorRole): AdministratorRoleData;
}

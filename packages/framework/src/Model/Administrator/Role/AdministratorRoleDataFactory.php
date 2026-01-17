<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Role;

class AdministratorRoleDataFactory
{
    public function createInstance(): AdministratorRoleData
    {
        return new AdministratorRoleData();
    }

    public function create(): AdministratorRoleData
    {
        return $this->createInstance();
    }

    public function createFromAdministratorRole(AdministratorRole $administratorRole): AdministratorRoleData
    {
        $administratorRoleData = $this->createInstance();
        $this->fillFromAdministratorRole($administratorRoleData, $administratorRole);

        return $administratorRoleData;
    }

    protected function fillFromAdministratorRole(
        AdministratorRoleData $administratorRoleData,
        AdministratorRole $administratorRole,
    ): void {
        $administratorRoleData->administrator = $administratorRole->getAdministrator();
        $administratorRoleData->role = $administratorRole->getRole();
    }
}

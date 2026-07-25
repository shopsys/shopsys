<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup;

class AdministratorRoleGroupDataFactory
{
    protected function createInstance(): AdministratorRoleGroupData
    {
        return new AdministratorRoleGroupData();
    }

    public function create(): AdministratorRoleGroupData
    {
        return $this->createInstance();
    }

    public function createFromAdministratorRoleGroup(
        AdministratorRoleGroup $administratorRoleGroup,
    ): AdministratorRoleGroupData {
        $administratorRoleGroupData = $this->createInstance();
        $this->fillFromAdministratorRoleGroup($administratorRoleGroupData, $administratorRoleGroup);

        return $administratorRoleGroupData;
    }

    protected function fillFromAdministratorRoleGroup(
        AdministratorRoleGroupData $administratorRoleGroupData,
        AdministratorRoleGroup $administratorRoleGroup,
    ): void {
        $administratorRoleGroupData->name = $administratorRoleGroup->getName();
        $administratorRoleGroupData->roles = $administratorRoleGroup->getRoles();
    }
}

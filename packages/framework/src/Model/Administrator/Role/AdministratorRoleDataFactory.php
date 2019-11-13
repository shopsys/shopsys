<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Role;

class AdministratorRoleDataFactory implements AdministratorRoleDataFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function create(): AdministratorRoleData
    {
        return new AdministratorRoleData();
    }

    /**
     * @inheritDoc
     */
    public function createFromAdministratorRole(AdministratorRole $administratorRole): AdministratorRoleData
    {
        $administratorRoleData = new AdministratorRoleData();
        $this->fillFromAdministratorRole($administratorRoleData, $administratorRole);

        return $administratorRoleData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleData $administratorRoleData
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole $administratorRole
     */
    protected function fillFromAdministratorRole(
        AdministratorRoleData $administratorRoleData,
        AdministratorRole $administratorRole
    ): void {
        $administratorRoleData->administrator = $administratorRole->getAdministrator();
        $administratorRoleData->role = $administratorRole->getRole();
    }
}

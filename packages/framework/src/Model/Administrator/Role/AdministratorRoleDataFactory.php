<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Role;

class AdministratorRoleDataFactory implements AdministratorRoleDataFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createInstance(): AdministratorRoleData
    {
        return new AdministratorRoleData();
    }

    /**
     * {@inheritdoc}
     */
    public function create(): AdministratorRoleData
    {
        return $this->createInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function createFromAdministratorRole(AdministratorRole $administratorRole): AdministratorRoleData
    {
        $administratorRoleData = $this->createInstance();
        $this->fillFromAdministratorRole($administratorRoleData, $administratorRole);

        return $administratorRoleData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleData $administratorRoleData
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole $administratorRole
     */
    protected function fillFromAdministratorRole(
        AdministratorRoleData $administratorRoleData,
        AdministratorRole $administratorRole,
    ): void {
        $administratorRoleData->administrator = $administratorRole->getAdministrator();
        $administratorRoleData->role = $administratorRole->getRole();
    }
}

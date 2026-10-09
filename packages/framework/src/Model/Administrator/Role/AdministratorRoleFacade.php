<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Role;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class AdministratorRoleFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AdministratorRoleFactory $administratorRoleFactory,
        protected readonly AdministratorRoleDataFactory $administratorRoleDataFactory,
    ) {
    }

    /**
     * @param string[] $roles
     */
    public function refreshAdministratorRoles(Administrator $administrator, array $roles): void
    {
        // roles of an administrator with a role group are given by the group
        $roles = $administrator->getRoleGroup() === null ? $this->addAdminRoleIfMissing($administrator, $roles) : [];

        // existing roles are kept, removed roles are deleted as orphans, so an unchanged role is not deleted and created again
        $currentRolesByName = [];

        foreach ($administrator->getAdministratorRoles() as $administratorRole) {
            $currentRolesByName[$administratorRole->getRole()] = $administratorRole;
        }

        $newRoles = [];

        foreach (array_unique($roles) as $role) {
            $newRoles[] = $currentRolesByName[$role] ?? $this->createNewRole($administrator, $role);
        }

        $administrator->setRoles($newRoles);
        $this->em->flush();
    }

    /**
     * @param string[] $roles
     * @return string[]
     */
    protected function addAdminRoleIfMissing(Administrator $administrator, array $roles): array
    {
        $adminRole = SystemRole::ADMIN;

        if ($administrator->isSuperadmin() || in_array(SystemRole::SUPER_ADMIN, $roles, true)) {
            $adminRole = SystemRole::SUPER_ADMIN;
        }

        if (in_array($adminRole, $roles, true) === false) {
            $roles[] = $adminRole;
        }

        return $roles;
    }

    protected function createNewRole(Administrator $administrator, string $role): AdministratorRole
    {
        $administratorRoleData = $this->administratorRoleDataFactory->create();
        $administratorRoleData->administrator = $administrator;
        $administratorRoleData->role = $role;

        return $this->administratorRoleFactory->create($administratorRoleData);
    }
}

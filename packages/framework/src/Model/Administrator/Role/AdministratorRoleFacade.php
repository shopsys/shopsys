<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Role;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class AdministratorRoleFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AdministratorRoleFactory $administratorRoleFactory,
        protected readonly AdministratorRoleDataFactory $administratorRoleDataFactory,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param array<int, string> $roles
     */
    public function refreshAdministratorRoles(Administrator $administrator, array $roles): void
    {
        $roles = $this->addAdminRoleIfMissing($administrator, $roles);

        $this->removeAllByAdministrator($administrator);

        if ($administrator->getRoleGroup() !== null) {
            $administrator->setRolesChangedNow();
            $this->em->flush();

            return;
        }

        $newRoles = [];

        foreach ($roles as $role) {
            $newRoles[] = $this->createNewRole($administrator, $role);
        }
        $administrator->addRoles($newRoles);

        $this->em->flush();
    }

    /**
     * @param array<int, string> $roles
     * @return array<int, string>
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

    protected function removeAllByAdministrator(Administrator $administrator): void
    {
        $oldAdministratorRoles = $administrator->getAdministratorRoles();

        foreach ($oldAdministratorRoles as $oldAdministratorRole) {
            $this->em->remove($oldAdministratorRole);
        }
        $this->em->flush();
    }

    protected function createNewRole(Administrator $administrator, string $role): AdministratorRole
    {
        $administratorRoleData = $this->administratorRoleDataFactory->create();
        $administratorRoleData->administrator = $administrator;
        $administratorRoleData->role = $role;

        return $this->administratorRoleFactory->create($administratorRoleData);
    }
}

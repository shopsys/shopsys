<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Role;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Security\Roles;

class AdministratorRoleFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleFactoryInterface $administratorRoleFactory
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleDataFactoryInterface $administratorRoleDataFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AdministratorRoleFactoryInterface $administratorRoleFactory,
        protected readonly AdministratorRoleDataFactoryInterface $administratorRoleDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param string[] $roles
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
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param string[] $roles
     * @return string[]
     */
    protected function addAdminRoleIfMissing(Administrator $administrator, array $roles): array
    {
        $adminRole = Roles::ROLE_ADMIN;

        if ($administrator->isSuperadmin()) {
            $adminRole = Roles::ROLE_SUPER_ADMIN;
        }

        if (in_array($adminRole, $roles, true) === false) {
            $roles[] = $adminRole;
        }

        return $roles;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    protected function removeAllByAdministrator(Administrator $administrator): void
    {
        $oldAdministratorRoles = $administrator->getAdministratorRoles();

        foreach ($oldAdministratorRoles as $oldAdministratorRole) {
            $this->em->remove($oldAdministratorRole);
        }
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param string $role
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole
     */
    protected function createNewRole(Administrator $administrator, string $role): AdministratorRole
    {
        $administratorRoleData = $this->administratorRoleDataFactory->create();
        $administratorRoleData->administrator = $administrator;
        $administratorRoleData->role = $role;

        return $this->administratorRoleFactory->create($administratorRoleData);
    }
}

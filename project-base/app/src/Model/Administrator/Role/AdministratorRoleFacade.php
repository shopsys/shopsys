<?php

declare(strict_types=1);

namespace App\Model\Administrator\Role;

use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleFacade as BaseAdministratorRoleFacade;

/**
 * @method string[] addAdminRoleIfMissing(\App\Model\Administrator\Administrator $administrator, string[] $roles)
 * @method removeAllByAdministrator(\App\Model\Administrator\Administrator $administrator)
 * @method \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole createNewRole(\App\Model\Administrator\Administrator $administrator, string $role)
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleFactory $administratorRoleFactory, \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleDataFactory $administratorRoleDataFactory)
 */
class AdministratorRoleFacade extends BaseAdministratorRoleFacade
{
    /**
     * @param \App\Model\Administrator\Administrator $administrator
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
}

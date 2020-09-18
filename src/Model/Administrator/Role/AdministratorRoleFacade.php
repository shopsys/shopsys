<?php

declare(strict_types=1);


namespace App\Model\Administrator\Role;

use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleFacade as BaseAdministratorRoleFacade;
use Shopsys\FrameworkBundle\Model\Security\Roles;

/**
 * @method refreshAdministratorRoles(\App\Model\Administrator\Administrator $administrator, string[] $roles)
 * @method removeAllByAdministrator(\App\Model\Administrator\Administrator $administrator)
 * @method \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole createNewRole(\App\Model\Administrator\Administrator $administrator, string $role)
 */
class AdministratorRoleFacade extends BaseAdministratorRoleFacade
{
    protected function addAdminRoleIfMissing(Administrator $administrator, array $roles): array
    {
        $adminRole = null;
        if ($administrator->isSuperadmin()) {
            $adminRole = Roles::ROLE_SUPER_ADMIN;
        }

        if ($adminRole !== null && in_array($adminRole, $roles, true) === false) {
            $roles[] = $adminRole;
        }

        return $roles;
    }
}

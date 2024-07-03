<?php

declare(strict_types=1);

namespace App\Model\Administrator\Role;

use Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleFacade as BaseAdministratorRoleFacade;

/**
 * @method string[] addAdminRoleIfMissing(\App\Model\Administrator\Administrator $administrator, string[] $roles)
 * @method removeAllByAdministrator(\App\Model\Administrator\Administrator $administrator)
 * @method \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole createNewRole(\App\Model\Administrator\Administrator $administrator, string $role)
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleFactory $administratorRoleFactory, \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleDataFactory $administratorRoleDataFactory)
 * @method refreshAdministratorRoles(\App\Model\Administrator\Administrator $administrator, string[] $roles)
 */
class AdministratorRoleFacade extends BaseAdministratorRoleFacade
{
}

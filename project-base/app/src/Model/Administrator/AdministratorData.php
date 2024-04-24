<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use App\Model\Administrator\RoleGroup\AdministratorRoleGroup;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData as BaseAdministratorData;

class AdministratorData extends BaseAdministratorData
{
    /**
     * @var \App\Model\Administrator\RoleGroup\AdministratorRoleGroup|null
     */
    public ?AdministratorRoleGroup $roleGroup;

    public function __construct()
    {
        parent::__construct();

        $this->roleGroup = null;
    }
}

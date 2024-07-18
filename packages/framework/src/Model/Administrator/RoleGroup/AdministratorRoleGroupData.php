<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup;

class AdministratorRoleGroupData
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string[]
     */
    public $roles = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup $administratorRoleGroup
     */
    public function fillFromEntity(AdministratorRoleGroup $administratorRoleGroup): void
    {
        $this->name = $administratorRoleGroup->getName();
        $this->roles = $administratorRoleGroup->getRoles();
    }
}

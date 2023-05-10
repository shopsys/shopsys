<?php

declare(strict_types=1);

namespace App\Model\Administrator\RoleGroup;

class AdministratorRoleGroupData
{
    /**
     * @var string|null
     */
    public ?string $name;

    /**
     * @var string[]
     */
    public $roles = [];

    /**
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroup $administratorRoleGroup
     */
    public function fillFromEntity(AdministratorRoleGroup $administratorRoleGroup): void
    {
        $this->name = $administratorRoleGroup->getName();
        $this->roles = $administratorRoleGroup->getRoles();
    }
}

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
}

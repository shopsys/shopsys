<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role\Exception;

use RuntimeException;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;

class RoleCannotBeOverwrittenException extends RuntimeException
{
    public function __construct(Role $role)
    {
        parent::__construct(sprintf('Role "%s" setting cannot be overwritten as this role is protected', $role->getName()));
    }
}

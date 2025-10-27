<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

enum ActionType: string
{
    case LIST = 'list';
    case DETAIL = 'detail';
    case CREATE = 'create';
    case EDIT = 'edit';
    case DELETE = 'delete';

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Permission
     */
    public function toPermission(): Permission
    {
        return match ($this) {
            self::LIST, self::DETAIL => Permission::VIEW,
            self::CREATE => Permission::CREATE,
            self::EDIT => Permission::EDIT,
            self::DELETE => Permission::DELETE,
        };
    }
}

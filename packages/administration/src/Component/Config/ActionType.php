<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

enum ActionType: string
{
    case LIST = 'list';
    case DETAIL = 'detail';
    case CREATE = 'create';
    case EDIT = 'edit';
    case DELETE = 'delete';

    public function toPermission(): Permission
    {
        return match ($this) {
            self::LIST, self::DETAIL => Permission::VIEW,
            self::CREATE => Permission::CREATE,
            self::EDIT => Permission::EDIT,
            self::DELETE => Permission::DELETE,
        };
    }

    /**
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Attribute\PermissionAttributeInterface>
     */
    public function toAccessControlRules(): array
    {
        return match ($this) {
            self::LIST, self::DETAIL => [new CanView()],
            self::CREATE => [new CanCreate()],
            self::EDIT => [
                new CanEdit(methods: [HttpMethod::POST]),
                new CanView(methods: [HttpMethod::GET]),
            ],
            self::DELETE => [new CanDelete()],
        };
    }
}

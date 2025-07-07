<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role;

/**
 * Helper for generating and parsing role identifiers with permissions
 */
final class RoleIdentifierHelper
{
    /**
     * Get role identifier with specific permission (e.g., ROLE_ORDER_VIEW)
     *
     * @param string $roleConstant
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\Permission $permission
     * @return string
     */
    public static function getIdentifierWithPermission(string $roleConstant, Permission $permission): string
    {
        return sprintf('%s_%s', $roleConstant, $permission->value);
    }
}

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
     */
    public static function getIdentifierWithPermission(string $roleConstant, Permission $permission): string
    {
        return sprintf('%s_%s', $roleConstant, $permission->value);
    }

    /**
     * Extract base role from role identifier (e.g., ROLE_ORDER_VIEW -> ROLE_ORDER)
     */
    public static function getRoleConstantFromIdentifier(string $roleIdentifier): string
    {
        $permissionValues = array_map(fn (Permission $permission) => $permission->value, Permission::cases());

        foreach ($permissionValues as $permissionValue) {
            $suffix = '_' . $permissionValue;

            if (str_ends_with($roleIdentifier, $suffix)) {
                return substr($roleIdentifier, 0, -strlen($suffix));
            }
        }

        // No permission suffix found, return original identifier
        return $roleIdentifier;
    }

    /**
     * Extract permission from role identifier (e.g., ROLE_ORDER_VIEW -> Permission::VIEW)
     */
    public static function getPermissionFromIdentifier(string $roleIdentifier): ?Permission
    {
        foreach (Permission::cases() as $permission) {
            $suffix = '_' . $permission->value;

            if (str_ends_with($roleIdentifier, $suffix)) {
                return $permission;
            }
        }

        return null;
    }
}

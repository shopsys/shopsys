<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role;

use Webmozart\Assert\Assert;

enum Permission: string
{
    case VIEW = 'VIEW';
    case CREATE = 'CREATE';
    case EDIT = 'EDIT';
    case DELETE = 'DELETE';
    case FULL = 'FULL';

    public function getName(): string
    {
        return match ($this) {
            self::VIEW => t('view'),
            self::CREATE => t('create'),
            self::EDIT => t('edit'),
            self::DELETE => t('delete'),
            self::FULL => t('full'),
        };
    }

    /**
     * Get subordinate permissions that are included by this permission
     *
     * Permission hierarchy:
     * - FULL: Includes all other permissions (VIEW, EDIT, CREATE, DELETE)
     * - CREATE: Includes VIEW (users need to see data to create new items)
     * - EDIT: Includes VIEW (users need to see data to edit it)
     * - DELETE: Includes VIEW (users need to see data to delete it)
     * - VIEW: No subordinate permissions (base level)
     *
     * @return self[]
     */
    public function getSubordinatePermissions(bool $onlyDirect = false): array
    {
        $directSubordinates = match ($this) {
            self::FULL => [self::EDIT, self::CREATE, self::DELETE],
            self::CREATE, self::EDIT, self::DELETE => [self::VIEW],
            default => [],
        };

        if ($onlyDirect) {
            return $directSubordinates;
        }

        return self::traversePermissionHierarchy($directSubordinates, fn ($p) => $p->getSubordinatePermissions(true));
    }

    /**
     * Check if this permission includes another permission
     *
     * This method implements the RBAC permission hierarchy where higher-level permissions
     * automatically include lower-level permissions. For example:
     * - FULL includes everything
     * - EDIT includes VIEW
     * - CREATE includes VIEW
     * - DELETE includes VIEW
     *
     * @param self $permission The permission to check
     * @return bool True if this permission includes the requested permission
     */
    public function includes(self $permission): bool
    {
        // A permission always includes itself
        if ($this === $permission) {
            return true;
        }

        // FULL permission includes everything
        if ($this === self::FULL) {
            return true;
        }

        foreach ($this->getSubordinatePermissions(true) as $subordinatePermission) {
            if ($subordinatePermission->includes($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Expand a list of permissions to include all subordinate permissions
     *
     * This method takes a list of permissions and expands it to include all subordinate permissions
     * for each permission in the list. It returns a flat array of unique permissions.
     *
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission> $permissions
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission>
     */
    public static function expand(array $permissions): array
    {
        Assert::allIsInstanceOf($permissions, self::class);

        return self::traversePermissionHierarchy($permissions, fn ($p) => $p->getSubordinatePermissions(true));
    }

    /**
     * Get the highest-level permissions for a given set of permissions
     *
     * For a role with permissions [VIEW, EDIT, CREATE], this returns [EDIT, CREATE]
     * because those are the highest-level permissions that don't have subordinates within the role
     *
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Permission[]
     */
    public static function getHighestLevelPermissions(array $permissions): array
    {
        Assert::allIsInstanceOf($permissions, self::class);

        $result = [];

        // FULL is always the highest permission
        if (in_array(self::FULL, $permissions, true)) {
            return [self::FULL];
        }

        foreach ($permissions as $permission) {
            $isHighest = true;

            foreach ($permissions as $otherPermission) {
                if ($otherPermission !== $permission && $otherPermission->includes($permission)) {
                    $isHighest = false;

                    break;
                }
            }

            if ($isHighest) {
                $result[$permission->value] = $permission;
            }
        }

        return array_values($result);
    }

    /**
     * @return string[]
     */
    public static function toValues(Permission|string ...$permissions): array
    {
        return array_map(
            fn (Permission|string $permission) => $permission instanceof self ? $permission->value : $permission,
            $permissions,
        );
    }

    /**
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission>
     */
    public static function fromValues(Permission|string ...$values): array
    {
        $permissions = [];

        foreach ($values as $value) {
            $permissions[] = $value instanceof self ? $value : self::from($value);
        }

        return $permissions;
    }

    /**
     * Generic method to traverse permission hierarchy using stack-based approach
     *
     * @param array<self> $startingPermissions
     * @param callable(self): array<self> $getChildrenCallback
     * @return array<self>
     */
    protected static function traversePermissionHierarchy(
        array $startingPermissions,
        callable $getChildrenCallback,
    ): array {
        $visited = [];
        $stack = $startingPermissions;

        while ($stack !== []) {
            $permission = array_pop($stack);

            if (isset($visited[$permission->value])) {
                continue;
            }

            $visited[$permission->value] = $permission;

            foreach ($getChildrenCallback($permission) as $child) {
                $stack[] = $child;
            }
        }

        return array_values($visited);
    }
}

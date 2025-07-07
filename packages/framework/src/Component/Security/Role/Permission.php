<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role;

enum Permission: string
{
    case VIEW = 'VIEW';
    case CREATE = 'CREATE';
    case EDIT = 'EDIT';
    case DELETE = 'DELETE';
    case FULL = 'FULL';

    /**
     * @return string
     */
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
     * @param self|string ...$values
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission>
     */
    public static function createFromValues(Permission|string ...$values): array
    {
        $permissions = [];

        foreach ($values as $value) {
            if ($value instanceof self) {
                // If it's already a Permission instance, use it directly
                $permissions[] = $value;

                continue;
            }
            $permissions[] = self::from($value);
        }

        return $permissions;
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
     * @param bool $onlyDirect
     * @return self[]
     */
    public function getSubordinatePermissions(bool $onlyDirect = false): array
    {
        $directSubordinates = match ($this) {
            self::FULL => [self::EDIT, self::CREATE, self::DELETE],
            self::CREATE => [self::VIEW],
            self::EDIT => [self::VIEW],
            self::DELETE => [self::VIEW],
            default => [],
        };

        if ($onlyDirect === true) {
            return $directSubordinates;
        }

        $subordinatePermissions = [];

        foreach ($directSubordinates as $subordinate) {
            $subordinatePermissions[] = $subordinate;

            $nextLevelSubordinates = $subordinate->getSubordinatePermissions();

            $subordinatePermissions = array_merge(
                $subordinatePermissions,
                array_filter($nextLevelSubordinates, function ($nextLevelSubordinate) use ($subordinatePermissions) {
                    return !in_array($nextLevelSubordinate, $subordinatePermissions, true);
                }),
            );
        }

        return $subordinatePermissions;
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
        // FULL permission includes everything
        if ($this === self::FULL) {
            return true;
        }

        // A permission always includes itself
        if ($this === $permission) {
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
     * Get the highest-level permissions for a given set of permissions
     *
     * For a role with permissions [VIEW, EDIT, CREATE], this returns [EDIT, CREATE]
     * because those are the highest-level permissions that don't have subordinates within the role
     *
     * @param array $permissions
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Permission[]
     */
    public static function getHighestLevelPermissions(array $permissions): array
    {
        $highestPermissions = [];

        $permissions = self::createFromValues(...$permissions);

        foreach ($permissions as $permission) {
            if ($permission === self::FULL) {
                return [$permission]; // FULL is always the highest permission
            }

            $isHighest = true;

            // Check if this permission is subordinate to any other permission in the role
            foreach ($permissions as $otherPermission) {
                if ($otherPermission !== $permission && $otherPermission->includes($permission)) {
                    $isHighest = false;

                    break;
                }
            }

            if ($isHighest) {
                $highestPermissions[] = $permission;
            }
        }

        return $highestPermissions;
    }
}

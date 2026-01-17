<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\DataTransformer;

use Override;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;
use Symfony\Component\Form\DataTransformerInterface;

final class RoleRowDataTransformer implements DataTransformerInterface
{
    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission> $availablePermissions
     */
    public function __construct(
        private readonly Role $role,
        private readonly array $availablePermissions = [],
    ) {
    }

    /**
     * Transforms role identifiers for a specific role to flat form structure
     *
     * @param array<string>|mixed $value Array of role identifiers for this role
     * @return array<string, bool> Flat form structure [roleConstant|permission] = bool
     */
    #[Override]
    public function transform(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        if ($this->role->isSingleRole()) {
            return [$this->role->getConstant() => in_array($this->role->getConstant(), $value, true)];
        }

        $rolePermissions = array_fill_keys(
            array_map(fn (Permission $permission) => $permission->value, $this->role->getAvailablePermissions()),
            false,
        );

        foreach ($value as $roleIdentifier) {
            if (!is_string($roleIdentifier)) {
                continue;
            }

            $permission = RoleIdentifierHelper::getPermissionFromIdentifier($roleIdentifier);

            if ($permission === null || !$this->role->hasPermission($permission)) {
                continue;
            }

            $rolePermissions[$permission->value] = true;

            foreach ($permission->getSubordinatePermissions() as $subordinatePermission) {
                if (isset($rolePermissions[$subordinatePermission->value])) {
                    $rolePermissions[$subordinatePermission->value] = true;
                }
            }
        }

        // Handle FULL permission logic
        if ($this->role->shouldIncludeFullPermission()) {
            $allPermissionsChecked = count(array_filter($rolePermissions, fn (bool $isChecked) => !$isChecked)) === 0;
            $rolePermissions[Permission::FULL->value] = $allPermissionsChecked;
        }

        $enabledPermissions = Permission::toValues(...$this->availablePermissions);

        return array_filter(
            $rolePermissions,
            fn (string $permissionValue) => in_array($permissionValue, $enabledPermissions, true),
            ARRAY_FILTER_USE_KEY,
        );
    }

    /**
     * Transforms flat form data back to role identifiers for this specific role
     *
     * @param array<string, mixed>|mixed $value Flat form data [roleConstant|permission] = bool
     * @return array<string> Array of role identifiers for this role
     */
    #[Override]
    public function reverseTransform(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        if ($this->role->isSingleRole()) {
            $roleValue = $value[$this->role->getConstant()] ?? false;

            return is_bool($roleValue) && $roleValue === true ? [$this->role->getConstant()] : [];
        }

        /** @var string[] $filteredCheckedPermissions */
        $filteredCheckedPermissions = array_keys(
            array_filter($value, function (mixed $isChecked, string $permission) {
                return $isChecked && in_array(Permission::tryFrom($permission), $this->availablePermissions, true);
            }, ARRAY_FILTER_USE_BOTH),
        );

        $result = [];

        foreach ($this->role->calculateHighestValidPermissions(Permission::fromValues(...$filteredCheckedPermissions)) as $permission) {
            $result[] = RoleIdentifierHelper::getIdentifierWithPermission($this->role->getConstant(), $permission);
        }

        return $result;
    }
}

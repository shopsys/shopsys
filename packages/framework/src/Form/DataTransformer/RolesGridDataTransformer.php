<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\DataTransformer;

use Override;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Webmozart\Assert\Assert;

final class RolesGridDataTransformer implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface $roleRegistry
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $context
     * @param array<string> $excludedRoles
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission> $availablePermissions
     */
    public function __construct(
        private readonly RoleRegistryInterface $roleRegistry,
        private readonly string $context,
        private readonly array $excludedRoles = [],
        private readonly array $availablePermissions = [],
    ) {
        Assert::allIsInstanceOf($availablePermissions, Permission::class);
    }

    /**
     * Transforms role identifiers array to multidimensional form structure
     *
     * @param array<string>|mixed $value Array of role identifiers (e.g., ['ROLE_ORDER_VIEW', 'ROLE_PRODUCT_FULL'])
     * @return array<string, array<string, bool>> Multidimensional array [roleConstant][permission] = bool
     */
    #[Override]
    public function transform(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $allRolePermissions = [];

        // Initialize all roles with their available permissions
        foreach ($this->roleRegistry->getRoles($this->context) as $role) {
            if (in_array($role->getConstant(), $this->excludedRoles, true)) {
                continue;
            }

            $allRolePermissions[$role->getConstant()] = array_fill_keys(
                array_map(fn (Permission $permission) => $permission->value, $role->getAvailablePermissions()),
                false,
            );
        }

        // Populate permissions based on provided role identifiers
        foreach ($value as $roleIdentifier) {
            if (!is_string($roleIdentifier)) {
                continue; // Skip invalid identifiers
            }

            $permission = RoleIdentifierHelper::getPermissionFromIdentifier($roleIdentifier);
            $roleConstant = RoleIdentifierHelper::getRoleConstantFromIdentifier($roleIdentifier);

            $role = $this->roleRegistry->getRole($roleConstant, $this->context);

            if (in_array($roleConstant, $this->excludedRoles, true)) {
                continue;
            }

            if ($permission === null || $role->hasPermission($permission) === false) {
                // If the role does not have this permission, skip it
                continue;
            }

            $allRolePermissions[$role->getConstant()][$permission->value] = true;

            foreach ($permission->getSubordinatePermissions() as $subordinatePermission) {
                $allRolePermissions[$role->getConstant()][$subordinatePermission->value] = true;
            }
        }

        foreach ($allRolePermissions as $roleName => $permissions) {
            $role = $this->roleRegistry->getRole($roleName, $this->context);

            if ($role->shouldIncludeFullPermission()) {
                $allRolePermissions[$roleName][Permission::FULL->value] = count(array_filter($permissions, fn (bool $isChecked) => !$isChecked)) === 0;
            }
        }

        return $allRolePermissions;
    }

    /**
     * Transforms multidimensional form data back to role identifiers array
     *
     * @param array<string, array<string, mixed>>|mixed $value Form data [roleConstant][permission] = bool
     * @return array<string> Array of role identifiers (e.g., ['ROLE_ORDER_VIEW', 'ROLE_PRODUCT_FULL'])
     */
    #[Override]
    public function reverseTransform(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];

        foreach ($value as $roleBase => $permissions) {
            if (!is_array($permissions)) {
                continue;
            }

            if (in_array($roleBase, $this->excludedRoles, true)) {
                continue;
            }

            $role = $this->roleRegistry->getRole($roleBase, $this->context);

            /** @var string[] $filteredCheckedPermissions */
            $filteredCheckedPermissions = array_keys(
                array_filter($permissions, function (mixed $isChecked, string $permission) {
                    return $isChecked && in_array(Permission::tryFrom($permission), $this->availablePermissions, true);
                }, ARRAY_FILTER_USE_BOTH),
            );

            $filteredPermissions = Permission::createFromValues(...$filteredCheckedPermissions);

            foreach ($role->calculateHighestValidPermissions($filteredPermissions) as $permission) {
                $result[] = RoleIdentifierHelper::getIdentifierWithPermission($role->getConstant(), $permission);
            }
        }

        return $result;
    }
}

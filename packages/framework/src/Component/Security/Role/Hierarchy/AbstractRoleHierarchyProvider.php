<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role\Hierarchy;

use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;

/**
 * Abstract base class for role hierarchy providers
 */
abstract class AbstractRoleHierarchyProvider
{
    /**
     * @var array<string, string[]>|null
     */
    protected ?array $generatedRoleHierarchy = null;

    /**
     * Build the specific role hierarchy for this provider
     *
     * @return array<string, string[]>
     */
    abstract protected function buildRoleHierarchy(): array;

    /**
     * @return class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>
     */
    abstract public function getTargetContext(): string;

    /**
     * @return array<string, string[]>
     */
    public function generateRoleHierarchy(): array
    {
        if ($this->generatedRoleHierarchy === null) {
            $this->generatedRoleHierarchy = $this->buildRoleHierarchy();
        }

        return $this->generatedRoleHierarchy;
    }

    /**
     * @param array<string, string[]> $hierarchy
     */
    protected function appendPermissionHierarchy(array &$hierarchy, Role $role, Permission $permission): void
    {
        $parentIdentifier = RoleIdentifierHelper::getIdentifierWithPermission($role->getConstant(), $permission);

        foreach ($permission->getSubordinatePermissions(true) as $subordinatePermission) {
            $childIdentifier = RoleIdentifierHelper::getIdentifierWithPermission($role->getConstant(), $subordinatePermission);
            $hierarchy[$parentIdentifier][] = $childIdentifier;

            $this->appendPermissionHierarchy($hierarchy, $role, $subordinatePermission);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\Role[] $regularRoles
     * @return array<string, string[]>
     */
    protected function buildPermissionHierarchy(array $regularRoles): array
    {
        /** @var array<string, string[]> $hierarchy */
        $hierarchy = [];

        foreach ($regularRoles as $role) {
            foreach ($role->getHighestLevelPermissions() as $permission) {
                $this->appendPermissionHierarchy($hierarchy, $role, $permission);
            }
        }

        return $hierarchy;
    }
}

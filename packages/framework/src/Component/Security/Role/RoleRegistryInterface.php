<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role;

/**
 * Interface for role registry that manages roles organized by context
 */
interface RoleRegistryInterface
{
    /**
     * Get roles for specific context
     *
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $context Context class name
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    public function getRoles(string $context): array;

    /**
     * Get identifiers of roles in specific context that offer any of the given permissions
     *
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $context
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\Permission[] $permissions
     * @return string[] Role identifiers with permission (e.g., 'ROLE_PRODUCT_EDIT')
     */
    public function getRoleIdentifiersWithAnyPermission(string $context, array $permissions): array;

    /**
     * Get a valid role by full role identifier for specific context
     * Validates both role existence and permission availability
     *
     * @param string $roleIdentifier Full role identifier (e.g., 'ROLE_ADMIN', 'ROLE_PRODUCT_VIEW')
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $context
     * @throws \InvalidArgumentException if role is not found or permission is not available
     */
    public function getRole(string $roleIdentifier, string $context): Role;
}

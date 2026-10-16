<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\AccessControl;

use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * The file manager is a shared tool of all WYSIWYG editors, so it is not bound to a single section.
 * It is available to every administrator who is allowed to create or edit something in any section.
 */
class FileManagerAccessChecker
{
    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly RoleHierarchyInterface $roleHierarchy,
        protected readonly RoleRegistryInterface $roleRegistry,
    ) {
    }

    public function hasAccess(): bool
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            return false;
        }

        // the hierarchy expands granted roles (e.g. ROLE_PRODUCT_FULL or a super admin) to all included permissions
        $reachableRoleNames = $this->roleHierarchy->getReachableRoleNames($token->getRoleNames());

        return array_intersect($reachableRoleNames, $this->getRoleIdentifiersGrantingAccess()) !== [];
    }

    /**
     * Identifiers are built from the registered roles and their permissions, not guessed from role names,
     * so a role that only happens to end with "_EDIT" does not grant access
     *
     * @return string[]
     */
    protected function getRoleIdentifiersGrantingAccess(): array
    {
        $roleIdentifiers = [];

        foreach ($this->roleRegistry->getRoles(AdminContext::class) as $role) {
            foreach ([Permission::CREATE, Permission::EDIT] as $permission) {
                if ($role->hasPermission($permission)) {
                    $roleIdentifiers[] = RoleIdentifierHelper::getIdentifierWithPermission($role->getConstant(), $permission);
                }
            }
        }

        return $roleIdentifiers;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\AccessControl;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class AccessChecker implements AccessCheckerInterface
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        private RoleRegistryInterface $roleRegistry,
        private RouteAccessCheckerInterface $routeAccessChecker,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function canView(string $roleConstant): bool
    {
        return $this->hasPermission($roleConstant, Permission::VIEW);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function canEdit(string $roleConstant): bool
    {
        return $this->hasPermission($roleConstant, Permission::EDIT);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function canCreate(string $roleConstant): bool
    {
        return $this->hasPermission($roleConstant, Permission::CREATE);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function canDelete(string $roleConstant): bool
    {
        return $this->hasPermission($roleConstant, Permission::DELETE);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function hasPermission(string $roleConstant, Permission $permission): bool
    {
        $role = $this->roleRegistry->getRole($roleConstant, AdminContext::class);

        if ($role->isSingleRole()) {
            return $this->authorizationChecker->isGranted($roleConstant);
        }

        $roleWithPermission = RoleIdentifierHelper::getIdentifierWithPermission($roleConstant, $permission);

        return $this->authorizationChecker->isGranted($roleWithPermission);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->authorizationChecker->isGranted($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function denyUnlessCanView(string $roleConstant): void
    {
        $this->denyUnlessHasPermission($roleConstant, Permission::VIEW);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function denyUnlessCanEdit(string $roleConstant): void
    {
        $this->denyUnlessHasPermission($roleConstant, Permission::EDIT);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function denyUnlessCanCreate(string $roleConstant): void
    {
        $this->denyUnlessHasPermission($roleConstant, Permission::CREATE);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function denyUnlessCanDelete(string $roleConstant): void
    {
        $this->denyUnlessHasPermission($roleConstant, Permission::DELETE);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function denyUnlessHasPermission(string $roleConstant, Permission $permission): void
    {
        if (!$this->hasPermission($roleConstant, $permission)) {
            throw new AccessDeniedException(
                sprintf('Access denied. Required permission: %s:%s', $roleConstant, $permission->value),
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function denyUnlessHasAnyRole(array $roles): void
    {
        if (!$this->hasAnyRole($roles)) {
            throw new AccessDeniedException(
                sprintf('Access denied. Required any of roles: %s', implode(', ', $roles)),
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function hasAccessToRoute(string $route, HttpMethod|string $method): bool
    {
        return $this->routeAccessChecker->hasAccess($route, $method);
    }
}

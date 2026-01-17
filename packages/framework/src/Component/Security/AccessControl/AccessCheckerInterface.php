<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\AccessControl;

use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

interface AccessCheckerInterface
{
    public function canView(string $roleConstant): bool;

    public function canEdit(string $roleConstant): bool;

    public function canCreate(string $roleConstant): bool;

    public function canDelete(string $roleConstant): bool;

    public function hasPermission(string $roleConstant, Permission $permission): bool;

    /**
     * @param string[] $roles
     */
    public function hasAnyRole(array $roles): bool;

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function denyUnlessCanView(string $roleConstant): void;

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function denyUnlessCanEdit(string $roleConstant): void;

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function denyUnlessCanCreate(string $roleConstant): void;

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function denyUnlessCanDelete(string $roleConstant): void;

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function denyUnlessHasPermission(string $roleConstant, Permission $permission): void;

    /**
     * Enforce that user has any of the specified roles (throws exception if denied)
     *
     * @param string[] $roles
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function denyUnlessHasAnyRole(array $roles): void;

    public function hasAccessToRoute(string $route, HttpMethod|string $method): bool;
}

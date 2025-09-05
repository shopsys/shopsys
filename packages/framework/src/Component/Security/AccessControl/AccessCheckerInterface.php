<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\AccessControl;

use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

interface AccessCheckerInterface
{
    /**
     * @param string $roleConstant
     * @return bool
     */
    public function canView(string $roleConstant): bool;

    /**
     * @param string $roleConstant
     * @return bool
     */
    public function canEdit(string $roleConstant): bool;

    /**
     * @param string $roleConstant
     * @return bool
     */
    public function canCreate(string $roleConstant): bool;

    /**
     * @param string $roleConstant
     * @return bool
     */
    public function canDelete(string $roleConstant): bool;

    /**
     * @param string $roleConstant
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\Permission $permission
     * @return bool
     */
    public function hasPermission(string $roleConstant, Permission $permission): bool;

    /**
     * @param string[] $roles
     * @return bool
     */
    public function hasAnyRole(array $roles): bool;

    /**
     * @param string $roleConstant
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function denyUnlessCanView(string $roleConstant): void;

    /**
     * @param string $roleConstant
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function denyUnlessCanEdit(string $roleConstant): void;

    /**
     * @param string $roleConstant
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function denyUnlessCanCreate(string $roleConstant): void;

    /**
     * @param string $roleConstant
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function denyUnlessCanDelete(string $roleConstant): void;

    /**
     * @param string $roleConstant
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\Permission $permission
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

    /**
     * @param string $route
     * @param \Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod|string $method
     * @return bool
     */
    public function hasAccessToRoute(string $route, HttpMethod|string $method): bool;
}

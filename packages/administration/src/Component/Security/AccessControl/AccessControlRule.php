<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\AccessControl;

use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;
use Webmozart\Assert\Assert;

/**
 * Enhanced representation of access control rule for caching and processing
 * Represents a validated role with optional permission and HTTP method restrictions
 * This class is used internally by the access control system and should not be used directly in attributes
 *
 * @internal
 */
final readonly class AccessControlRule
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\Role $role Validated role object
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\Permission|null $permission Specific permission if applicable
     * @param \Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod[] $httpMethods Validated HTTP methods (empty array means all methods)
     */
    public function __construct(
        public Role $role,
        public ?Permission $permission,
        public array $httpMethods,
    ) {
        Assert::allIsInstanceOf($this->httpMethods, HttpMethod::class);
    }

    /**
     * Check if this rule applies to the given HTTP method
     */
    public function appliesToMethod(HttpMethod $httpMethod): bool
    {
        // Empty methods array means rule applies to all methods
        if ($this->httpMethods === []) {
            return true;
        }

        // Check if the method is in our validated methods
        foreach ($this->httpMethods as $method) {
            if ($method === $httpMethod) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the role identifier string (with permission suffix if applicable)
     */
    public function getRoleIdentifier(): string
    {
        if ($this->permission !== null) {
            return RoleIdentifierHelper::getIdentifierWithPermission($this->role->getConstant(), $this->permission);
        }

        return $this->role->getConstant();
    }

    /**
     * Check if user with given role checker callback has access
     *
     * @param callable(string): bool $hasRoleCallback
     */
    public function hasAccess(HttpMethod $httpMethod, callable $hasRoleCallback): bool
    {
        // Check if rule applies to this HTTP method
        if (!$this->appliesToMethod($httpMethod)) {
            return false;
        }

        // Check if user has the required role
        return $hasRoleCallback($this->getRoleIdentifier());
    }
}

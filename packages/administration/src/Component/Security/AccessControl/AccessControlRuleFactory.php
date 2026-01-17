<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\AccessControl;

use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;

/**
 * Factory for creating validated AccessControlRule objects
 */
final class AccessControlRuleFactory
{
    public function __construct(
        private readonly RoleRegistryInterface $roleRegistry,
    ) {
    }

    /**
     * Create an AccessControlRule with validation
     *
     * @param string $roleIdentifier Role identifier (e.g., 'ROLE_ADMIN', 'ROLE_PRODUCT_VIEW')
     * @param array<string|\Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod> $methods HTTP methods this rule applies to
     * @throws \InvalidArgumentException
     */
    public function create(string $roleIdentifier, array $methods = []): AccessControlRule
    {
        $validatedMethods = HttpMethod::validateMethods($methods);

        return new AccessControlRule(
            role: $this->roleRegistry->getRole($roleIdentifier, AdminContext::class),
            permission: RoleIdentifierHelper::getPermissionFromIdentifier($roleIdentifier),
            httpMethods: $validatedMethods,
        );
    }
}

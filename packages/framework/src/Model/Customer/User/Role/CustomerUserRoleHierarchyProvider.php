<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Override;
use Shopsys\FrameworkBundle\Component\Context\FrontendApiContext;
use Shopsys\FrameworkBundle\Component\Security\Role\Hierarchy\AbstractRoleHierarchyProvider;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;

/**
 * Generates role hierarchy for Frontend API context
 */
class CustomerUserRoleHierarchyProvider extends AbstractRoleHierarchyProvider
{
    public function __construct(
        protected readonly RoleRegistryInterface $roleRegistry,
    ) {
    }

    /**
     * @return class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>
     */
    #[Override]
    public function getTargetContext(): string
    {
        return FrontendApiContext::class;
    }

    /**
     * @return array<string, string[]>
     */
    #[Override]
    protected function buildRoleHierarchy(): array
    {
        $regularRoles = $this->getRegularRoles();

        return array_merge(
            $this->buildSystemRoleHierarchy($regularRoles),
            $this->buildCustomerManagementRoleHierarchy(),
            $this->buildPermissionHierarchy($regularRoles),
        );
    }

    /**
     * @return array<string, string[]>
     */
    protected function buildCustomerManagementRoleHierarchy(): array
    {
        return [
            CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS => [CustomerUserRole::ROLE_API_CUSTOMER_SELF_MANAGE],
        ];
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Role> $regularRoles
     * @return array<string, string[]>
     */
    protected function buildSystemRoleHierarchy(array $regularRoles): array
    {
        /** @var string[] $roles */
        $roles = [];

        foreach ($regularRoles as $role) {
            if ($role->isSingleRole()) {
                $roles[] = $role->getConstant();
            }
        }

        return [
            CustomerUserRole::ROLE_API_ALL => $roles,
        ];
    }

    /**
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Role\Role>
     */
    protected function getRegularRoles(): array
    {
        return array_filter(
            $this->roleRegistry->getRoles($this->getTargetContext()),
            static fn (Role $role): bool => $role->getConstant() !== CustomerUserRole::ROLE_API_ALL,
        );
    }
}

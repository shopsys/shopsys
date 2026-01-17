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
final class CustomerUserRoleHierarchyProvider extends AbstractRoleHierarchyProvider
{
    public function __construct(
        private readonly RoleRegistryInterface $roleRegistry,
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
            $this->buildPermissionHierarchy($regularRoles),
        );
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Role> $regularRoles
     * @return array<string, string[]>
     */
    private function buildSystemRoleHierarchy(array $regularRoles): array
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
    private function getRegularRoles(): array
    {
        return array_filter(
            $this->roleRegistry->getRoles($this->getTargetContext()),
            static fn (Role $role): bool => $role->getConstant() !== CustomerUserRole::ROLE_API_ALL,
        );
    }
}

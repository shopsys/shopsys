<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\Role;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Security\Role\Hierarchy\AbstractRoleHierarchyProvider;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;

/**
 * Generates role hierarchy for admin context
 */
final class AdminRoleHierarchyProvider extends AbstractRoleHierarchyProvider
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
        return AdminContext::class;
    }

    /**
     * @return array<string, string[]>
     */
    #[Override]
    protected function buildRoleHierarchy(): array
    {
        $regularRoles = $this->getRegularRoles();

        return array_merge_recursive(
            $this->buildSystemRoleHierarchy(),
            $this->buildAggregateRoleHierarchy($regularRoles),
            $this->buildPermissionHierarchy($regularRoles),
            $this->buildContentEditorMediaHierarchy(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\Role[] $regularRoles
     * @return array<string, string[]>
     */
    private function buildAggregateRoleHierarchy(array $regularRoles): array
    {
        /** @var string[] $fullRoles */
        $fullRoles = [];
        /** @var string[] $viewRoles */
        $viewRoles = [];

        foreach ($regularRoles as $role) {
            if ($role->isSingleRole()) {
                $fullRoles[] = $role->getConstant();

                continue;
            }

            foreach ($role->getHighestLevelPermissions() as $permission) {
                $fullRoles[] = RoleIdentifierHelper::getIdentifierWithPermission($role->getConstant(), $permission);
            }

            if ($role->hasPermission(Permission::VIEW)) {
                $viewRoles[] = RoleIdentifierHelper::getIdentifierWithPermission($role->getConstant(), Permission::VIEW);
            }
        }

        return [
            SystemRole::ALL => [SystemRole::ALL_VIEW, ...$fullRoles],
            SystemRole::ALL_VIEW => $viewRoles,
        ];
    }

    /**
     * @return array<string, string[]>
     */
    private function buildSystemRoleHierarchy(): array
    {
        return [
            SystemRole::SUPER_ADMIN => [SystemRole::ADMIN, SystemRole::ALL],
        ];
    }

    /**
     * The media picker of the content editor lists and uploads images, so whoever edits the content needs the media rights
     *
     * @return array<string, string[]>
     */
    private function buildContentEditorMediaHierarchy(): array
    {
        $mediaIdentifiers = [
            RoleIdentifierHelper::getIdentifierWithPermission(AdminRoleConstant::ROLE_MEDIA, Permission::VIEW),
            RoleIdentifierHelper::getIdentifierWithPermission(AdminRoleConstant::ROLE_MEDIA, Permission::CREATE),
        ];
        $hierarchy = [];

        foreach ([AdminRoleConstant::ROLE_ARTICLE, AdminRoleConstant::ROLE_BLOG_ARTICLE, AdminRoleConstant::ROLE_MAIL_TEMPLATE] as $roleConstant) {
            $hierarchy[RoleIdentifierHelper::getIdentifierWithPermission($roleConstant, Permission::EDIT)] = $mediaIdentifiers;
        }

        return $hierarchy;
    }

    /**
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Role\Role>
     */
    private function getRegularRoles(): array
    {
        return array_filter(
            $this->roleRegistry->getRoles($this->getTargetContext()),
            static fn (Role $role): bool => !in_array($role->getConstant(), SystemRole::getAll(), true),
        );
    }
}

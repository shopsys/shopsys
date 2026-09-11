<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\Role\CoreRoleProviderInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleCollection;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;

final class CrudAdminRoleProvider implements CoreRoleProviderInterface
{
    public function __construct(
        private readonly CrudControllerRegistry $crudControllerRegistry,
    ) {
    }

    #[Override]
    public function getPriority(): int
    {
        return -1;
    }

    /**
     * @return class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>
     */
    #[Override]
    public function getTargetContext(): string
    {
        return AdminContext::class;
    }

    #[Override]
    public function configureRoles(RoleCollection $roleCollection): void
    {
        foreach ($this->crudControllerRegistry->getAll() as $crudControllerDefinition) {
            $config = $crudControllerDefinition->config;

            if ($config->isFullDisabled() || $config->getCustomRoleConstant() !== null) {
                continue;
            }

            // the permissions the access control rules of the enabled actions require on the role of the CRUD controller
            // must be available on it, rules of other roles (explicit role, ForRole of the handling class) do not influence it
            $roleConstant = $crudControllerDefinition->getRoleConstant();
            $requiredPermissions = [];

            foreach ($crudControllerDefinition->actions as $action) {
                if (!$config->isActionEnabled($action->name)) {
                    continue;
                }

                foreach ($action->accessControlRules as $rule) {
                    $permission = RoleIdentifierHelper::getPermissionFromIdentifier($rule->roleIdentifier);

                    if ($permission !== null && RoleIdentifierHelper::getRoleConstantFromIdentifier($rule->roleIdentifier) === $roleConstant) {
                        $requiredPermissions[] = $permission;
                    }
                }
            }

            $role = new Role(
                $crudControllerDefinition->getRoleConstant(),
                $config->getMenuTitle(),
                Permission::getHighestLevelPermissions($requiredPermissions),
            );

            $role->setRoleSection($config->getCustomRoleSection() ?? $config->getMenuSection());
            $role->setOverwritable(false);

            $roleCollection->add($role);
        }
    }
}

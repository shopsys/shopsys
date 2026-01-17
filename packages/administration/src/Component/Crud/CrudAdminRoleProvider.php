<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\Role\CoreRoleProviderInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleCollection;

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
        foreach ($this->crudControllerRegistry->getItems() as $crudControllerDefinition) {
            $config = $crudControllerDefinition->getConfig();

            if ($config->isFullDisabled() || $config->getCustomRoleConstant() !== null) {
                continue;
            }

            $requiredPermissions = [];

            foreach ($config->getActions() as $actionType) {
                $requiredPermissions[] = $actionType->toPermission();
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

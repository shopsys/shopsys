<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud;

/**
 * Reads the role constants of CRUD controllers resolved at compile time by ResolveCrudRoleConstantsCompilerPass
 */
final class CrudRoleConstantProvider
{
    public const string CRUD_ROLE_CONSTANTS_PARAMETER = 'shopsys.admin.crud_role_constants';

    /**
     * @param array<class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, array{roleConstant: string, customRoleConstant: string|null}> $roleConstants
     */
    public function __construct(
        private readonly array $roleConstants = [],
    ) {
    }

    /**
     * Returns the role constant guarding a CRUD controller (its built-in actions and custom routes alike), or null when the class is not a registered CRUD controller
     *
     * @param class-string $controllerClass
     */
    public function findRoleConstant(string $controllerClass): ?string
    {
        return $this->roleConstants[$controllerClass]['roleConstant'] ?? null;
    }

    /**
     * Returns the role declared by the ForRole attribute on the CRUD controller or one of its extensions, or null when the controller uses its generated role
     *
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     */
    public function findCustomRoleConstant(string $controllerClass): ?string
    {
        return $this->roleConstants[$controllerClass]['customRoleConstant'] ?? null;
    }
}

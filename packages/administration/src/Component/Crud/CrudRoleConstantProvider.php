<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud;

/**
 * Reads the custom role constants of CRUD controllers resolved at compile time by ResolveCrudRoleConstantsCompilerPass
 */
final class CrudRoleConstantProvider
{
    public const string CRUD_ROLE_CONSTANTS_PARAMETER = 'shopsys.admin.crud_role_constants';

    /**
     * @param array<class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>, string|null> $customRoleConstants role declared by the ForRole attribute, or null for the generated role, indexed by controller class
     */
    public function __construct(
        private readonly array $customRoleConstants = [],
    ) {
    }

    /**
     * Returns the role declared by the ForRole attribute on the CRUD controller or one of its extensions, or null when the controller uses its generated role
     *
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     */
    public function findCustomRoleConstant(string $controllerClass): ?string
    {
        return $this->customRoleConstants[$controllerClass] ?? null;
    }
}

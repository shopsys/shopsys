<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudEntityIdentifierExtractor;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Shared behavior of CRUD controllers and their extensions: access to the Definition of the CRUD controller
 * and helpers for linking between its actions.
 *
 * Expects to be used in a controller extending Symfony\Bundle\FrameworkBundle\Controller\AbstractController
 * as it relies on its generateUrl() and redirect() methods.
 */
trait CrudControllerTrait
{
    /**
     * Definition of the CRUD controller, set by CrudControllerInitializer before the action runs.
     * Not available in configure() as the config is part of the Definition itself.
     */
    protected Definition $definition;

    #[Required]
    public CrudEntityIdentifierExtractor $crudEntityIdentifierExtractor;

    #[Required]
    public RouteCsrfProtector $routeCsrfProtector;

    public function setDefinition(Definition $definition): void
    {
        $this->definition = $definition;
    }

    /**
     * Returns the route name of the given action of this CRUD controller (e.g. "admin_crud_order_edit")
     */
    protected function getCrudRouteName(ActionType $actionType): string
    {
        return CrudTransformationHelper::generateRouteName($this->definition->controllerName, $actionType);
    }

    /**
     * Generates URL of the given action of this CRUD controller.
     * Actions working with a single record (detail, edit, delete) expect the entity or its ID.
     * CSRF token is added automatically for protected actions (e.g. delete), so the URL is directly usable.
     *
     * @param array<string, mixed> $parameters additional route parameters
     */
    protected function generateCrudUrl(
        ActionType $actionType,
        int|object|null $entityOrId = null,
        array $parameters = [],
    ): string {
        $routeName = $this->getCrudRouteName($actionType);

        if ($entityOrId !== null) {
            $parameters['id'] = is_object($entityOrId) ? $this->crudEntityIdentifierExtractor->getId($entityOrId) : $entityOrId;
        }

        if ($this->routeCsrfProtector->isActionProtected($this->definition->controllerClass, $actionType->value . 'Action')) {
            $parameters[RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER] ??= $this->routeCsrfProtector->getCsrfTokenByRoute($routeName);
        }

        return $this->generateUrl($routeName, $parameters);
    }

    /**
     * Redirects to the given action of this CRUD controller, see generateCrudUrl() for the parameters
     *
     * @param array<string, mixed> $parameters additional route parameters
     */
    protected function redirectToCrudAction(
        ActionType $actionType,
        int|object|null $entityOrId = null,
        array $parameters = [],
    ): RedirectResponse {
        return $this->redirect($this->generateCrudUrl($actionType, $entityOrId, $parameters));
    }
}

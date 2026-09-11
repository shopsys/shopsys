<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use InvalidArgumentException;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudEntityIdentifierExtractor;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
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
     * Returns the route name of the given action of this CRUD controller (e.g. "admin_crud_order_edit"),
     * custom actions are referenced by their name
     */
    protected function getCrudRouteName(ActionType|string $action): string
    {
        return $this->definition->getAction($action)->getRouteName();
    }

    /**
     * Generates URL of the given action of this CRUD controller, custom actions are referenced by their name.
     * Actions working with a single record (detail, edit, delete, custom actions with {id} in the path) expect the entity or its ID.
     * CSRF token is added automatically for protected actions (e.g. delete), so the URL is directly usable.
     *
     * @param array<string, mixed> $parameters additional route parameters
     */
    protected function generateCrudUrl(
        ActionType|string $action,
        int|object|null $entityOrId = null,
        array $parameters = [],
    ): string {
        $actionDefinition = $this->definition->getAction($action);

        if ($actionDefinition->entityBound !== ($entityOrId !== null || isset($parameters['id']))) {
            throw new InvalidArgumentException(sprintf(
                $actionDefinition->entityBound
                    ? 'Action "%s" of "%s" works with a single record, pass the entity or its ID.'
                    : 'Action "%s" of "%s" does not work with a single record, do not pass an entity or ID.',
                $actionDefinition->name,
                $this->definition->controllerClass,
            ));
        }

        if ($entityOrId !== null) {
            $parameters['id'] = is_object($entityOrId) ? $this->crudEntityIdentifierExtractor->getId($entityOrId) : $entityOrId;
        }

        if ($this->routeCsrfProtector->isActionProtected($actionDefinition->controllerClass, $actionDefinition->method)) {
            $parameters[RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER] ??= $this->routeCsrfProtector->getCsrfTokenByRoute($actionDefinition->getRouteName());
        }

        return $this->generateUrl($actionDefinition->getRouteName(), $parameters);
    }

    /**
     * Redirects to the given action of this CRUD controller, see generateCrudUrl() for the parameters
     *
     * @param array<string, mixed> $parameters additional route parameters
     */
    protected function redirectToCrudAction(
        ActionType|string $action,
        int|object|null $entityOrId = null,
        array $parameters = [],
    ): RedirectResponse {
        return $this->redirect($this->generateCrudUrl($action, $entityOrId, $parameters));
    }

    /**
     * Loads a record the same way the built-in edit and delete actions do (through the registered handler),
     * so custom actions apply the same checks
     */
    protected function getCrudEntity(int $id): Presentable
    {
        return $this->definition->getReadHandler()->getById($id);
    }
}

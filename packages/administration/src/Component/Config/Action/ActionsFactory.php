<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action;

use InvalidArgumentException;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\ActionRouteInterface;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\CrudActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\RouteActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\UrlActionRouteData;
use Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionRegistry;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Symfony\Component\Routing\RouterInterface;

final class ActionsFactory
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionRegistry $crudControllerDefinitionRegistry
     * @param \Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider $crudRouteProvider
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(
        private readonly CrudControllerDefinitionRegistry $crudControllerDefinitionRegistry,
        private readonly CrudRouteProvider $crudRouteProvider,
        private readonly RouterInterface $router,
    ) {
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractAction[] $actions
     * @param object $entity
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionData[]
     */
    public function processActions(array $actions, ?object $entity = null): array
    {
        $actionsToReturn = [];

        foreach ($actions as $action) {
            $actionData = $this->processAction($action->getData(), $entity);

            if ($actionData !== null) {
                $actionsToReturn[] = $actionData;
            }
        }

        return $actionsToReturn;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionData $actionData
     * @param object|null $entity
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionData|null
     */
    private function processAction(
        ActionData $actionData,
        ?object $entity = null,
    ): ?ActionData {
        if ($this->checkActionVisibility($actionData, $entity) === false) {
            return null;
        }

        if ($actionData->actionRoute === null) {
            return $actionData;
        }

        $actionData->setUrl($this->generateUrl($actionData->actionRoute, $entity));

        return $actionData;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionData $actionData
     * @param object|null $entity
     * @return bool
     */
    private function checkActionVisibility(
        ActionData $actionData,
        ?object $entity = null,
    ): bool {
        return $actionData->displayIf === null || call_user_func($actionData->displayIf, $entity) !== false;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\ActionRouteInterface $actionRoute
     * @param object|null $entity
     * @return string
     */
    private function generateUrl(ActionRouteInterface $actionRoute, ?object $entity): string
    {
        if ($actionRoute instanceof UrlActionRouteData) {
            return $actionRoute->getUrl($entity);
        }

        if ($actionRoute instanceof CrudActionRouteData) {
            $routeItem = $this->crudRouteProvider->generate(
                $this->crudControllerDefinitionRegistry->getItem($actionRoute->getCrudController()),
                $actionRoute->getActionType(),
            );

            $parameters = $actionRoute->getId($entity) !== null ? ['entityId' => $actionRoute->getId($entity)] : [];

            return $this->router->generate($routeItem->getRouteName(), $parameters);
        }

        if ($actionRoute instanceof RouteActionRouteData) {
            return $this->router->generate($actionRoute->getRouteName(), $actionRoute->getRouteParameters($entity));
        }

        throw new InvalidArgumentException('Action has invalid route type');
    }
}

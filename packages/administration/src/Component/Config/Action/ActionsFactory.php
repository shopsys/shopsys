<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action;

use InvalidArgumentException;
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
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractActionBuilder[] $actions
     * @param object $entity
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionData[]
     */
    public function processEntityActions(array $actions, object $entity): array
    {
        $actionsToReturn = [];
        $actionType = ActionType::ENTITY;

        foreach ($actions as $action) {
            $actionData = $this->processAction($action->getData(), $actionType);

            if ($actionData !== null) {
                $actionsToReturn[] = $actionData;
            }
        }

        return $actionsToReturn;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractActionBuilder[] $actions
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionData[]
     */
    public function processGlobalActions(array $actions): array
    {
        $actionsToReturn = [];
        $actionType = ActionType::GLOBAL;

        foreach ($actions as $action) {
            $actionData = $this->processAction($action->getData(), $actionType);

            if ($actionData !== null) {
                $actionsToReturn[] = $actionData;
            }
        }

        return $actionsToReturn;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractActionBuilder[] $actions
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionData[]
     */
    public function processDatagridActions(array $actions): array
    {
        $actionsToReturn = [];

        foreach ($actions as $action) {
            $data = $action->getData();

            if ($this->checkActionVisibility($data, ActionType::DATAGRID) === false || $data->routeType === ActionRouteType::NONE) {
                continue;
            }


            if ($data->routeType === ActionRouteType::URL) {
                $data->setLinkUrl($data->url);
            }

            if ($data->routeType === ActionRouteType::CRUD) {
                $routeItem = $this->crudRouteProvider->generate($this->crudControllerDefinitionRegistry->getItem($data->crudController), $data->pageType);
                $data->setLinkUrl($routeItem->getRouteName());
            }

            if ($data->routeType === ActionRouteType::ROUTE) {
                $data->setLinkUrl($data->route);
            }

            $actionsToReturn[] = $data;
        }

        return $actionsToReturn;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionData $actionData
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionType $actionType
     * @param object|null $entity
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionData|null
     */
    private function processAction(ActionData $actionData, ActionType $actionType, ?object $entity = null): ?ActionData
    {
        if ($this->checkActionVisibility($actionData, $actionType, $entity) === false) {
            return null;
        }

        if ($actionData->routeType === ActionRouteType::NONE) {
            return $actionData;
        }

        if ($actionData->routeType === ActionRouteType::URL) {
            if (is_callable($actionData->url)) {
                $actionData->setLinkUrl(call_user_func($actionData->url, $entity));
            } else {
                $actionData->setLinkUrl($actionData->url);
            }
        }

        if ($actionData->routeType === ActionRouteType::CRUD) {
            $routeItem = $this->crudRouteProvider->generate($this->crudControllerDefinitionRegistry->getItem($actionData->crudController), $actionData->pageType);
            $actionData->setLinkUrl($this->router->generate($routeItem->getRouteName(), $actionData->pageId ? call_user_func($actionData->pageId, $entity) : []));
        }

        if ($actionData->routeType === ActionRouteType::ROUTE) {
            $routeParameters = is_callable($actionData->routeParameters) ? call_user_func($actionData->routeParameters, $entity) : $actionData->routeParameters;

            if (is_array($routeParameters) === false) {
                throw new InvalidArgumentException('Route parameters must be an array or a callable returning an array');
            }

            $actionData->setLinkUrl($this->router->generate($actionData->route, $routeParameters));
        }

        return $actionData;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionData $actionData
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionType $actionType
     * @param object|null $entity
     * @return bool
     */
    private function checkActionVisibility(
        ActionData $actionData,
        ActionType $actionType,
        ?object $entity = null,
    ): bool {
        if ($actionData->actionType !== $actionType) {
            return false;
        }

        return $actionData->displayIf === null || call_user_func($actionData->displayIf, $entity) !== false;
    }
}

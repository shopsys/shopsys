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
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionBuilder[] $actions
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionType $actionType
     * @param object|null $entity
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionData[]
     */
    public function processActions(array $actions, ActionType $actionType, ?object $entity = null): array
    {
        if ($actionType === ActionType::ENTITY && $entity === null) {
            throw new InvalidArgumentException('Entity must be provided for entity type actions');
        }

        $actionsToReturn = [];

        foreach ($actions as $action) {
            $data = $action->getData();

            if ($data->actionType !== $actionType) {
                continue;
            }

            if ($data->displayIf !== null && call_user_func($data->displayIf, $entity) === false) {
                continue;
            }

            if ($data->routeType === ActionRouteType::NONE) {
                $actionsToReturn[] = $data;
                continue;
            }

            if ($data->routeType === ActionRouteType::URL) {
                if (is_callable($data->url)) {
                    $data->setLinkUrl(call_user_func($data->url, $entity));
                } else {
                    $data->setLinkUrl($data->url);
                }
            }

            if ($data->routeType === ActionRouteType::CRUD) {
                $routeItem = $this->crudRouteProvider->generate($this->crudControllerDefinitionRegistry->getItem($data->crudController), $data->pageType);
                $data->setLinkUrl($this->router->generate($routeItem->getRouteName(), $data->pageId ? call_user_func($data->pageId, $entity) : []));
            }

            if ($data->routeType === ActionRouteType::ROUTE) {
                $routeParameters = is_callable($data->routeParameters) ? call_user_func($data->routeParameters, $entity) : $data->routeParameters;

                if (is_array($routeParameters) === false) {
                    throw new InvalidArgumentException('Route parameters must be an array or a callable returning an array');
                }

                $data->setLinkUrl($this->router->generate($data->route, $routeParameters));
            }

            $actionsToReturn[] = $data;

        }

        return $actionsToReturn;
    }
}
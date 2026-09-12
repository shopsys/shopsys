<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use InvalidArgumentException;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Action\CrudActionDefinition;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Crud\CrudRegistryItem;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Symfony\Component\Routing\Route;

final class CrudRouteProvider
{
    public const string CRUD_DEFAULTS_PREFIX = '_crud_';
    public const string IS_CRUD_CONTROLLER = '_crud_controller';
    public const string CRUD_ACTION = '_crud_action';
    public const string CRUD_ROLE_CONSTANT = '_crud_role_constant';
    public const string CRUD_CONTROLLER_CLASS = '_crud_controller_class';

    public function __construct(
        private readonly CrudControllerRegistry $crudControllerRegistry,
        private readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * Returns the routes of all enabled actions of all CRUD controllers, indexed by "ControllerClass::actionName"
     *
     * @return array<string, \Shopsys\AdministrationBundle\Component\Router\CrudRouteItem>
     */
    public function getAll(): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            self::class,
            function () {
                $routeItems = [];

                foreach ($this->crudControllerRegistry->getAll() as $registryItem) {
                    foreach ($registryItem->actions as $action) {
                        if (!$registryItem->config->isActionEnabled($action->name)) {
                            continue;
                        }

                        $routeItems[$registryItem->controllerClass . '::' . $action->name] = $this->createRouteItem($registryItem, $action);
                    }
                }

                return $routeItems;
            },
            'all',
        );
    }

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType|string $action built-in action or the name of a custom action
     */
    public function getRouteItem(string $controllerClass, ActionType|string $action): CrudRouteItem
    {
        $actionName = CrudActionDefinition::normalizeName($action);
        $cacheKey = $controllerClass . '::' . $actionName;
        $allRouteItems = $this->getAll();

        if (!isset($allRouteItems[$cacheKey])) {
            throw new InvalidArgumentException(sprintf(
                'Route item for controller "%s" and action "%s" not found.',
                $controllerClass,
                $actionName,
            ));
        }

        return $allRouteItems[$cacheKey];
    }

    /**
     * The route options declared by the CrudAction attribute are applied first, so the CRUD defaults can never be overridden by them
     */
    private function createRouteItem(CrudRegistryItem $registryItem, CrudActionDefinition $action): CrudRouteItem
    {
        $route = new Route(
            $this->createControllerBasePath($registryItem) . $action->path,
            $action->defaults,
            $action->requirements,
            [],
            null,
            [],
            $action->methods,
            $action->condition ?? '',
        );
        $route->setDefault('_controller', $action->getControllerReference());
        $route->setDefault(self::IS_CRUD_CONTROLLER, true);
        $route->setDefault(self::CRUD_ACTION, $action->name);
        $route->setDefault(self::CRUD_ROLE_CONSTANT, $registryItem->getRoleConstant());
        $route->setDefault(self::CRUD_CONTROLLER_CLASS, $registryItem->controllerClass);

        return new CrudRouteItem(
            controller: $action->getControllerReference(),
            route: $route,
            routeName: CrudTransformationHelper::generateRouteName($registryItem->controllerName, $action->name),
            actionName: $action->name,
        );
    }

    /**
     * Returns "/{prefix}/{controller-name}" part shared by all routes of the CRUD controller
     */
    private function createControllerBasePath(CrudRegistryItem $registryItem): string
    {
        $routePath = '/';
        $routePrefix = $registryItem->config->getRoutePrefix();

        if ($routePrefix) {
            $routePath .= CrudTransformationHelper::transformToRouteUrl(trim($routePrefix, '/')) . '/';
        }

        return $routePath . CrudTransformationHelper::transformToRouteUrl($registryItem->controllerName);
    }
}

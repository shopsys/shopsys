<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use InvalidArgumentException;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Symfony\Component\Routing\Route;

final class CrudRouteProvider
{
    public const string IS_CRUD_CONTROLLER = '_crud_controller';
    public const string CRUD_ACTION = '_crud_action';
    public const string CRUD_ROLE_CONSTANT = '_crud_role_constant';

    /**
     * @var array<value-of<\Shopsys\AdministrationBundle\Component\Config\ActionType>, array{
     *     path: string,
     *     routeName: string,
     *     entityId: bool
     * }>
     */
    public const array DEFAULT_ROUTES_CONFIG = [
        ActionType::LIST->value => [
            'path' => '/',
            'routeName' => 'listAction',
            'entityId' => false,
        ],
        ActionType::DETAIL->value => [
            'path' => '/detail/{id}',
            'routeName' => 'detailAction',
            'entityId' => true,
        ],
        ActionType::CREATE->value => [
            'path' => '/create',
            'routeName' => 'createAction',
            'entityId' => false,
        ],
        ActionType::EDIT->value => [
            'path' => '/edit/{id}',
            'routeName' => 'editAction',
            'entityId' => true,
        ],
        ActionType::DELETE->value => [
            'path' => '/delete/{id}',
            'routeName' => 'deleteAction',
            'entityId' => true,
        ],
    ];

    public function __construct(
        private readonly CrudControllerRegistry $crudControllerRegistry,
        private readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @return array<string, \Shopsys\AdministrationBundle\Component\Router\CrudRouteItem>
     */
    public function getAll(): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            self::class,
            function () {
                $routeItems = [];

                foreach ($this->crudControllerRegistry->getAll() as $registryItem) {
                    foreach ($registryItem->config->getActions() as $actionType) {
                        $routeItem = $this->createRouteItem(
                            $registryItem->controllerClass,
                            $registryItem->controllerName,
                            $registryItem->config->getRoutePrefix(),
                            $registryItem->getRoleConstant(),
                            $actionType,
                        );

                        $cacheKey = $registryItem->controllerClass . '::' . $actionType->value;
                        $routeItems[$cacheKey] = $routeItem;
                    }
                }

                return $routeItems;
            },
            'all',
        );
    }

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     */
    public function getRouteItem(string $controllerClass, ActionType $pageType): CrudRouteItem
    {
        $cacheKey = $controllerClass . '::' . $pageType->value;
        $allRouteItems = $this->getAll();

        if (!isset($allRouteItems[$cacheKey])) {
            throw new InvalidArgumentException(sprintf(
                'Route item for controller "%s" and action "%s" not found.',
                $controllerClass,
                $pageType->value,
            ));
        }

        return $allRouteItems[$cacheKey];
    }

    private function createRouteItem(
        string $controllerClass,
        string $controllerName,
        ?string $routePrefix,
        string $roleConstant,
        ActionType $pageType,
    ): CrudRouteItem {
        $routeConfig = self::DEFAULT_ROUTES_CONFIG[$pageType->value];
        $routePath = '/';

        if ($routePrefix) {
            $routePath .= CrudTransformationHelper::transformToRouteUrl(trim($routePrefix, '/')) . '/';
        }

        $routePath .= CrudTransformationHelper::transformToRouteUrl($controllerName) . $routeConfig['path'];

        $route = new Route($routePath, [
            '_controller' => CrudTransformationHelper::generateController($controllerClass, $pageType),
        ]);

        $route->setDefault(self::IS_CRUD_CONTROLLER, true);
        $route->setDefault(self::CRUD_ACTION, $pageType->value);
        $route->setDefault(self::CRUD_ROLE_CONSTANT, $roleConstant);

        return new CrudRouteItem(
            controller: CrudTransformationHelper::generateController($controllerClass, $pageType),
            route: $route,
            routeName: CrudTransformationHelper::generateRouteName($controllerName, $pageType),
            pageType: $pageType,
        );
    }
}

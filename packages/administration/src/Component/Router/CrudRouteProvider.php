<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use Shopsys\AdministrationBundle\Component\Config\CrudConfigProvider;
use Shopsys\AdministrationBundle\Component\Config\PageType;
use Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem;
use Symfony\Component\Routing\Route;

final class CrudRouteProvider
{
    /**
     * @var array<value-of<\Shopsys\AdministrationBundle\Component\Config\PageType>, array{
     *     path: string,
     *     routeName: string,
     *     entityId: bool
     * }>
     */
    public const array DEFAULT_ROUTES_CONFIG = [
        PageType::LIST->value => [
            'path' => '/',
            'routeName' => 'listAction',
            'entityId' => false,
        ],
        PageType::DETAIL->value => [
            'path' => '/{entityId}',
            'routeName' => 'detailAction',
            'entityId' => true,
        ],
        PageType::CREATE->value => [
            'path' => '/create',
            'routeName' => 'createAction',
            'entityId' => false,
        ],
        PageType::EDIT->value => [
            'path' => '/{entityId}/edit',
            'routeName' => 'editAction',
            'entityId' => true,
        ],
        PageType::DELETE->value => [
            'path' => '/{entityId}/delete',
            'routeName' => 'deleteAction',
            'entityId' => true,
        ],
    ];

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\CrudConfigProvider $crudConfigProvider
     */
    public function __construct(
        private readonly CrudConfigProvider $crudConfigProvider,
    ) {
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem $item
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @return \Shopsys\AdministrationBundle\Component\Router\CrudRouteItem
     */
    public function generate(CrudControllerDefinitionItem $item, PageType $pageType): CrudRouteItem
    {
        $config = $this->crudConfigProvider->getConfig($item);

        return new CrudRouteItem(
            controller: $this->generateController($item->controllerClass, $pageType),
            route: $this->generateRoute($item, $pageType, $config->getRoutePrefix()),
            routeName: $this->generateRouteName($item->controllerName, $pageType),
            pageType: $pageType,
        );
    }

    /**
     * @param string $controllerName
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @return string
     */
    private function generateRouteName(string $controllerName, PageType $pageType): string
    {
        return 'admin_crud_' . $this->transformToRouteName($controllerName) . '_' . $pageType->value;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem $item
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @param string|null $routePrefix
     * @return \Symfony\Component\Routing\Route
     */
    private function generateRoute(CrudControllerDefinitionItem $item, PageType $pageType, ?string $routePrefix): Route
    {
        $routeConfig = self::DEFAULT_ROUTES_CONFIG[$pageType->value];
        $routePath = '/';

        if ($routePrefix) {
            $routePath .= $this->transformToRouteUrl(trim($routePrefix, '/')) . '/';
        }

        $routePath .= $this->transformToRouteUrl($item->controllerName) . $routeConfig['path'];

        return new Route(
            $routePath,
            [
                '_controller' => $this->generateController($item->controllerClass, $pageType),
            ],
        );
    }

    /**
     * @param string $controllerClass
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @return string
     */
    private function generateController(string $controllerClass, PageType $pageType): string
    {
        return sprintf('%s::%sAction', $controllerClass, $pageType->value);
    }

    /**
     * Transform CrudController name to string that can be used as part of route URL in kebab-case format
     *
     * Example:
     *     PriceListController -> price-list
     *     OrdersController -> orders
     *
     * @param string $controllerName
     * @return string
     */
    private function transformToRouteUrl(string $controllerName): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $this->getCleanControllerName($controllerName)));
    }

    /**
     * Transform CrudController name to string that can be used to define route name in snake_case format
     *
     * Example:
     *    PriceListController => price_list
     *    OrdersController => orders
     *
     * @param string $controllerName
     * @return string
     */
    private function transformToRouteName(string $controllerName): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $this->getCleanControllerName($controllerName)));
    }

    /**
     * Remove "CrudController" or "Controller" from controller name to be able to use it with routes
     *
     * @param string $controllerName
     * @return string
     */
    private function getCleanControllerName(string $controllerName): string
    {
        return str_replace(['CrudController', 'Controller'], '', $controllerName);
    }
}

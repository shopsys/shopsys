<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use ReflectionClass;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfigProvider;
use Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Symfony\Component\Routing\Route;
use Webmozart\Assert\Assert;

final class CrudRouteProvider
{
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
            'path' => '/{id}/detail',
            'routeName' => 'detailAction',
            'entityId' => true,
        ],
        ActionType::CREATE->value => [
            'path' => '/create',
            'routeName' => 'createAction',
            'entityId' => false,
        ],
        ActionType::EDIT->value => [
            'path' => '/{id}/edit',
            'routeName' => 'editAction',
            'entityId' => true,
        ],
        ActionType::DELETE->value => [
            'path' => '/{id}/delete',
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
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $pageType
     * @return \Shopsys\AdministrationBundle\Component\Router\CrudRouteItem
     */
    public function generate(CrudControllerDefinitionItem $item, ActionType $pageType): CrudRouteItem
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
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $crudController
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     * @return string
     */
    public function generateCrudRoute(string $crudController, ActionType $actionType): string
    {
        Assert::subclassOf($crudController, AbstractCrudController::class);

        $reflectionClass = new ReflectionClass($crudController);
        $crudControllerName = $reflectionClass->getShortName();

        return $this->generateRouteName($crudControllerName, $actionType);
    }

    /**
     * @param string $controllerName
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $pageType
     * @return string
     */
    private function generateRouteName(string $controllerName, ActionType $pageType): string
    {
        return 'admin_crud_' . $this->transformToRouteName($controllerName) . '_' . $pageType->value;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem $item
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $pageType
     * @param string|null $routePrefix
     * @return \Symfony\Component\Routing\Route
     */
    private function generateRoute(
        CrudControllerDefinitionItem $item,
        ActionType $pageType,
        ?string $routePrefix,
    ): Route {
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
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $pageType
     * @return string
     */
    private function generateController(string $controllerClass, ActionType $pageType): string
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

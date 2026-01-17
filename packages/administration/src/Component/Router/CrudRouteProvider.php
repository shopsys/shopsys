<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use Symfony\Component\Routing\Route;

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

    public function generate(Definition $item, ActionType $pageType): CrudRouteItem
    {
        return new CrudRouteItem(
            controller: CrudTransformationHelper::generateController($item->controllerClass, $pageType),
            route: $this->generateRoute($item, $pageType, $item->getConfig()->getRoutePrefix()),
            routeName: $this->generateRouteName($item->controllerName, $pageType),
            pageType: $pageType,
        );
    }

    private function generateRouteName(string $controllerName, ActionType $pageType): string
    {
        return 'admin_crud_' . CrudTransformationHelper::transformToRouteName($controllerName) . '_' . $pageType->value;
    }

    private function generateRoute(
        Definition $item,
        ActionType $pageType,
        ?string $routePrefix,
    ): Route {
        $routeConfig = self::DEFAULT_ROUTES_CONFIG[$pageType->value];
        $routePath = '/';

        if ($routePrefix) {
            $routePath .= CrudTransformationHelper::transformToRouteUrl(trim($routePrefix, '/')) . '/';
        }

        $routePath .= CrudTransformationHelper::transformToRouteUrl($item->controllerName) . $routeConfig['path'];

        return new Route(
            $routePath,
            [
                '_controller' => CrudTransformationHelper::generateController($item->controllerClass, $pageType),
            ],
        );
    }
}

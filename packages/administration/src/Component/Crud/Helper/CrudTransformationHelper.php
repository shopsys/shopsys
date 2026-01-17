<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Helper;

use Shopsys\AdministrationBundle\Component\Config\ActionType;

final class CrudTransformationHelper
{
    /**
     * Transform CrudController name to string that can be used to define route name in snake_case format
     *
     * Example:
     *    PriceListController => price_list
     *    OrdersController => orders
     */
    public static function transformToRouteName(string $controllerName): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', self::getCleanControllerName($controllerName)));
    }

    public static function generateController(string $controllerClass, ActionType $pageType): string
    {
        return sprintf('%s::%sAction', $controllerClass, $pageType->value);
    }

    /**
     * Transform CrudController name to string that can be used as part of route URL in kebab-case format
     *
     * Example:
     *     PriceListController -> price-list
     *     OrdersController -> orders
     */
    public static function transformToRouteUrl(string $controllerName): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', self::getCleanControllerName($controllerName)));
    }

    /**
     * Remove "CrudController" or "Controller" from controller name to be able to use it with routes
     */
    public static function getCleanControllerName(string $controllerName): string
    {
        return str_replace(['CrudController', 'Controller'], '', $controllerName);
    }
}

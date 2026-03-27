<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Helper;

use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Symfony\Component\String\Inflector\EnglishInflector;
use Symfony\Component\String\UnicodeString;

final class CrudTransformationHelper
{
    private static ?EnglishInflector $inflector = null;

    /**
     * Transform CrudController name to string that can be used to define route name in snake_case format
     *
     * Example:
     *    PriceListController => price_list
     *    OrdersController => orders
     */
    public static function transformToRouteName(string $controllerName): string
    {
        return (string)new UnicodeString(self::getCleanControllerName($controllerName))->snake();
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
        return (string)new UnicodeString(self::getCleanControllerName($controllerName))->kebab();
    }

    public static function generateRouteName(string $controllerName, ActionType $pageType): string
    {
        return sprintf('admin_crud_%s_%s', self::transformToRouteName($controllerName), $pageType->value);
    }

    public static function generateRoleConstant(string $controllerName, ?string $customRoleConstant = null): string
    {
        return $customRoleConstant ?? (string)new UnicodeString('ROLE_CRUD_' . self::transformToRouteName($controllerName))->upper();
    }

    /**
     * Converts PascalCase entity class short name to human-readable singular form (ucfirst).
     *
     * Example:
     *     OrderItem => Order item
     *     Category => Category
     */
    public static function toSingularEntityName(string $entityName): string
    {
        $humanized = (string)new UnicodeString($entityName)->snake()->replace('_', ' ');

        return ucfirst(self::getInflector()->singularize($humanized)[0]);
    }

    /**
     * Converts PascalCase entity class short name to human-readable plural form (ucfirst).
     *
     * Example:
     *     OrderItem => Order items
     *     Category => Categories
     */
    public static function toPluralEntityName(string $entityName): string
    {
        $humanized = (string)new UnicodeString($entityName)->snake()->replace('_', ' ');

        return ucfirst(self::getInflector()->pluralize($humanized)[0]);
    }

    /**
     * Remove "CrudController" or "Controller" from controller name to be able to use it with routes
     */
    private static function getCleanControllerName(string $controllerName): string
    {
        return str_replace(['CrudController', 'Controller'], '', $controllerName);
    }

    private static function getInflector(): EnglishInflector
    {
        return self::$inflector ??= new EnglishInflector();
    }
}

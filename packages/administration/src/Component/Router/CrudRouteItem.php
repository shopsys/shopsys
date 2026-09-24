<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use Symfony\Component\Routing\Route;

final class CrudRouteItem
{
    public function __construct(
        private readonly string $controller,
        private readonly Route $route,
        private readonly string $routeName,
        private readonly string $actionName,
    ) {
    }

    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * Returns a clone to prevent mutation of the cached original
     * (Symfony's RouteCollection::addPrefix() mutates Route objects in place)
     */
    public function getRoute(): Route
    {
        return clone $this->route;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Symfony\Component\Routing\Route;

final class CrudRouteItem
{
    public function __construct(
        private readonly string $controller,
        private readonly Route $route,
        private readonly string $routeName,
        private readonly ActionType $pageType,
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

    public function getPageType(): ActionType
    {
        return $this->pageType;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Symfony\Component\Routing\Route;

final class CrudRouteItem
{
    /**
     * @param string $controller
     * @param \Symfony\Component\Routing\Route $route
     * @param string $routeName
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $pageType
     */
    public function __construct(
        private readonly string $controller,
        private readonly Route $route,
        private readonly string $routeName,
        private readonly ActionType $pageType,
    ) {
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @return \Symfony\Component\Routing\Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\ActionType
     */
    public function getPageType(): ActionType
    {
        return $this->pageType;
    }
}

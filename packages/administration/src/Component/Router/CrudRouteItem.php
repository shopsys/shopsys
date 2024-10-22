<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use Shopsys\AdministrationBundle\Component\Config\PageType;
use Symfony\Component\Routing\Route;

final class CrudRouteItem
{
    /**
     * @param string $controller
     * @param \Symfony\Component\Routing\Route $route
     * @param string $routeName
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     */
    public function __construct(
        private readonly string $controller,
        private readonly Route $route,
        private readonly string $routeName,
        private readonly PageType $pageType,
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
     * @return \Shopsys\AdministrationBundle\Component\Config\PageType
     */
    public function getPageType(): PageType
    {
        return $this->pageType;
    }
}

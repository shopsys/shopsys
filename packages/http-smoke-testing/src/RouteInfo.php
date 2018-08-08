<?php

namespace Shopsys\HttpSmokeTesting;

use Symfony\Component\Routing\Route;

class RouteInfo
{
    /**
     * @var string
     */
    private $routeName;

    /**
     * @var \Symfony\Component\Routing\Route
     */
    private $route;

    public function __construct(string $routeName, Route $route)
    {
        $this->routeName = $routeName;
        $this->route = $route;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function getRoutePath(): string
    {
        return $this->route->getPath();
    }

    public function getRouteCondition(): string
    {
        return $this->route->getCondition();
    }

    public function isHttpMethodAllowed(string $method): bool
    {
        $methods = $this->route->getMethods();

        return count($methods) === 0 || in_array(strtoupper($method), $methods, true);
    }

    public function isRouteParameterRequired(string $name): bool
    {
        return !$this->route->hasDefault($name) && in_array($name, $this->getRouteParameterNames(), true);
    }

    /**
     * @return string[]
     */
    public function getRouteParameterNames(): array
    {
        $compiledRoute = $this->route->compile();

        return $compiledRoute->getVariables();
    }
}

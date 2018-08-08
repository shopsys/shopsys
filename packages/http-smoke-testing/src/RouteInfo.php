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

    /**
     * @param string $routeName
     */
    public function __construct($routeName, Route $route)
    {
        $this->routeName = $routeName;
        $this->route = $route;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function getRoutePath()
    {
        return $this->route->getPath();
    }

    public function getRouteCondition()
    {
        return $this->route->getCondition();
    }

    /**
     * @param string $method
     * @return bool
     */
    public function isHttpMethodAllowed($method)
    {
        $methods = $this->route->getMethods();

        return count($methods) === 0 || in_array(strtoupper($method), $methods, true);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isRouteParameterRequired($name)
    {
        return !$this->route->hasDefault($name) && in_array($name, $this->getRouteParameterNames(), true);
    }

    /**
     * @return string[]
     */
    public function getRouteParameterNames()
    {
        $compiledRoute = $this->route->compile();

        return $compiledRoute->getVariables();
    }
}

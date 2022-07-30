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
     * @var array<(\Shopsys\HttpSmokeTesting\Annotation\DataSet|\Shopsys\HttpSmokeTesting\Annotation\Skipped)>
     */
    private $annotations;

    /**
     * @param string $routeName
     * @param \Symfony\Component\Routing\Route $route
     * @param array<(\Shopsys\HttpSmokeTesting\Annotation\DataSet|\Shopsys\HttpSmokeTesting\Annotation\Skipped)> $annotations
     */
    public function __construct(string $routeName, Route $route, array $annotations = [])
    {
        $this->routeName = $routeName;
        $this->route = $route;
        $this->annotations = $annotations;
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @return string
     */
    public function getRoutePath(): string
    {
        return $this->route->getPath();
    }

    /**
     * @return string
     */
    public function getRouteCondition(): string
    {
        return $this->route->getCondition();
    }

    /**
     * @param string $method
     * @return bool
     */
    public function isHttpMethodAllowed(string $method): bool
    {
        $methods = $this->route->getMethods();

        return count($methods) === 0 || in_array(strtoupper($method), $methods, true);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isRouteParameterRequired(string $name): bool
    {
        return !$this->route->hasDefault($name) && in_array($name, $this->getRouteParameterNames(), true);
    }

    /**
     * @return array<string, mixed>
     */
    public function getRouteParameterNames(): array
    {
        return $this->route->compile()->getVariables();
    }

    /**
     * @return array<(\Shopsys\HttpSmokeTesting\Annotation\DataSet|\Shopsys\HttpSmokeTesting\Annotation\Skipped)>
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
    }
}

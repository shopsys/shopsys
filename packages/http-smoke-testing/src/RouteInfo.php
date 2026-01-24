<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting;

use Symfony\Component\Routing\Route;

class RouteInfo
{
    public function __construct(
        private string $routeName,
        private readonly Route $route,
        private readonly array $annotations = [],
    ) {
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

    public function getAnnotations(): array
    {
        return $this->annotations;
    }
}

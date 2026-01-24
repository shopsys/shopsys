<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting;

use Shopsys\HttpSmokeTesting\Exception\RouteNameNotFoundException;

class RouteConfigCustomizer
{
    /**
     * @param \Shopsys\HttpSmokeTesting\RequestDataSetGenerator[] $requestDataSetGenerators
     */
    public function __construct(private readonly array $requestDataSetGenerators)
    {
    }

    /**
     * Provided $callback will be called with RouteConfig and RouteInfo as arguments
     *
     * @see \Shopsys\HttpSmokeTesting\RouteConfig
     * @see \Shopsys\HttpSmokeTesting\RouteInfo
     */
    public function customize(callable $callback): self
    {
        foreach ($this->requestDataSetGenerators as $requestDataSetGenerator) {
            $callback($requestDataSetGenerator, $requestDataSetGenerator->getRouteInfo());
        }

        return $this;
    }

    /**
     * Provided $callback will be called with RouteConfig and RouteInfo that matches by route name as arguments
     *
     * @see \Shopsys\HttpSmokeTesting\RouteConfig
     * @see \Shopsys\HttpSmokeTesting\RouteInfo
     * @param string|string[] $routeName
     */
    public function customizeByRouteName(
        string|array $routeName,
        callable $callback,
    ): self {
        $routeNames = (array)$routeName;
        $foundRouteNames = [];

        foreach ($this->requestDataSetGenerators as $requestDataSetGenerator) {
            $routeInfo = $requestDataSetGenerator->getRouteInfo();

            if (!in_array($routeInfo->getRouteName(), $routeNames, true)) {
                continue;
            }

            $callback($requestDataSetGenerator, $routeInfo);
            $foundRouteNames[] = $routeInfo->getRouteName();
        }

        $notFoundRouteNames = array_diff($routeNames, $foundRouteNames);

        if (count($notFoundRouteNames) > 0) {
            throw new RouteNameNotFoundException($notFoundRouteNames);
        }

        return $this;
    }
}

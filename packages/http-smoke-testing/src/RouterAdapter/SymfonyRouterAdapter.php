<?php

namespace Shopsys\HttpSmokeTesting\RouterAdapter;

use Shopsys\HttpSmokeTesting\RequestDataSet;
use Shopsys\HttpSmokeTesting\RouteInfo;
use Symfony\Component\Routing\RouterInterface;

class SymfonyRouterAdapter implements RouterAdapterInterface
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return \Shopsys\HttpSmokeTesting\RouteInfo[]
     */
    public function getAllRouteInfo(): array
    {
        $allRouteInfo = [];
        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            $allRouteInfo[] = new RouteInfo($routeName, $route);
        }

        return $allRouteInfo;
    }

    public function generateUri(RequestDataSet $requestDataSet): string
    {
        return $this->router->generate($requestDataSet->getRouteName(), $requestDataSet->getParameters());
    }
}

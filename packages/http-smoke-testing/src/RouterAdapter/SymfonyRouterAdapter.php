<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\RouterAdapter;

use Override;
use ReflectionException;
use ReflectionMethod;
use Shopsys\HttpSmokeTesting\Attribute\DataSet;
use Shopsys\HttpSmokeTesting\Attribute\Skipped;
use Shopsys\HttpSmokeTesting\RequestDataSet;
use Shopsys\HttpSmokeTesting\RouteInfo;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class SymfonyRouterAdapter implements RouterAdapterInterface
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    /**
     * @return \Shopsys\HttpSmokeTesting\RouteInfo[]
     */
    #[Override]
    public function getAllRouteInfo(): array
    {
        $allRouteInfo = [];

        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            $allRouteInfo[] = new RouteInfo($routeName, $route, $this->extractAttributesForRoute($route));
        }

        return $allRouteInfo;
    }

    private function extractAttributesForRoute(Route $route): array
    {
        if ($route->hasDefault('_controller')) {
            return $this->extractAttributesForController($route->getDefault('_controller'));
        }

        return [];
    }

    private function extractAttributesForController(string $controller): array
    {
        try {
            $reflectionMethod = new ReflectionMethod($controller);
        } catch (ReflectionException $e) {
            return [];
        }

        return $this->getControllerMethodAttributes($reflectionMethod);
    }

    /**
     * @return array<\Shopsys\HttpSmokeTesting\Attribute\DataSet|\Shopsys\HttpSmokeTesting\Attribute\Skipped>
     */
    private function getControllerMethodAttributes(ReflectionMethod $reflectionMethod): array
    {
        $attributes = [];

        foreach ($reflectionMethod->getAttributes(DataSet::class) as $attribute) {
            $attributes[] = $attribute->newInstance();
        }

        foreach ($reflectionMethod->getAttributes(Skipped::class) as $attribute) {
            $attributes[] = $attribute->newInstance();
        }

        return $attributes;
    }

    #[Override]
    public function generateUri(RequestDataSet $requestDataSet): ?string
    {
        return $this->router->generate($requestDataSet->getRouteName(), $requestDataSet->getParameters(), UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

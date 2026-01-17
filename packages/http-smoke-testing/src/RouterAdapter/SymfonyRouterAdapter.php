<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\RouterAdapter;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Override;
use ReflectionException;
use ReflectionMethod;
use Shopsys\HttpSmokeTesting\Annotation\DataSet;
use Shopsys\HttpSmokeTesting\Annotation\Skipped;
use Shopsys\HttpSmokeTesting\RequestDataSet;
use Shopsys\HttpSmokeTesting\RouteInfo;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class SymfonyRouterAdapter implements RouterAdapterInterface
{
    private AnnotationReader $annotationsReader;

    public function __construct(private readonly RouterInterface $router)
    {
        AnnotationRegistry::registerLoader('class_exists');

        $this->annotationsReader = new AnnotationReader();
    }

    /**
     * @return \Shopsys\HttpSmokeTesting\RouteInfo[]
     */
    #[Override]
    public function getAllRouteInfo(): array
    {
        $allRouteInfo = [];

        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            $allRouteInfo[] = new RouteInfo($routeName, $route, $this->extractAnnotationsForRoute($route));
        }

        return $allRouteInfo;
    }

    private function extractAnnotationsForRoute(Route $route): array
    {
        if ($route->hasDefault('_controller')) {
            return $this->extractAnnotationForController($route->getDefault('_controller'));
        }

        return [];
    }

    private function extractAnnotationForController(string $controller): array
    {
        try {
            $reflectionMethod = new ReflectionMethod($controller);
        } catch (ReflectionException $e) {
            return [];
        }

        return $this->getControllerMethodAnnotations($reflectionMethod);
    }

    private function getControllerMethodAnnotations(ReflectionMethod $reflectionMethod): array
    {
        $annotations = [];

        foreach ($this->annotationsReader->getMethodAnnotations($reflectionMethod) as $annotation) {
            if ($annotation instanceof DataSet || $annotation instanceof Skipped) {
                $annotations[] = $annotation;
            }
        }

        return $annotations;
    }

    #[Override]
    public function generateUri(RequestDataSet $requestDataSet): ?string
    {
        return $this->router->generate($requestDataSet->getRouteName(), $requestDataSet->getParameters(), UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

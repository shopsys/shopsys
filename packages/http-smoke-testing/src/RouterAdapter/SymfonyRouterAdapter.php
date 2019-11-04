<?php

namespace Shopsys\HttpSmokeTesting\RouterAdapter;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Shopsys\HttpSmokeTesting\Annotation\DataSet;
use Shopsys\HttpSmokeTesting\Annotation\Skipped;
use Shopsys\HttpSmokeTesting\RequestDataSet;
use Shopsys\HttpSmokeTesting\RouteInfo;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class SymfonyRouterAdapter implements RouterAdapterInterface
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    private $annotationsReader;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        AnnotationRegistry::registerLoader('class_exists');

        $this->router = $router;
        $this->annotationsReader = new AnnotationReader();
    }

    /**
     * @return \Shopsys\HttpSmokeTesting\RouteInfo[]
     */
    public function getAllRouteInfo()
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
        $annotations = [];

        list($className, $methodName) = explode('::', $controller);

        if (method_exists($className, $methodName)) {
            $reflectionMethod = new \ReflectionMethod($className, $methodName);

            foreach ($this->annotationsReader->getMethodAnnotations($reflectionMethod) as $annotation) {
                if ($annotation instanceof DataSet || $annotation instanceof Skipped) {
                    $annotations[] = $annotation;
                }
            }
        }

        return $annotations;
    }

    /**
     * @param \Shopsys\HttpSmokeTesting\RequestDataSet $requestDataSet
     * @return string
     */
    public function generateUri(RequestDataSet $requestDataSet)
    {
        return $this->router->generate($requestDataSet->getRouteName(), $requestDataSet->getParameters());
    }
}

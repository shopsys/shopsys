<?php

namespace Shopsys\HttpSmokeTesting\RouterAdapter;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Shopsys\HttpSmokeTesting\Annotation\DataSet;
use Shopsys\HttpSmokeTesting\Annotation\Skipped;
use Shopsys\HttpSmokeTesting\RequestDataSet;
use Shopsys\HttpSmokeTesting\RouteInfo;
use Symfony\Component\Routing\RouterInterface;

class SymfonyRouterAdapter implements RouterAdapterInterface
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        AnnotationRegistry::registerLoader('class_exists');

        $this->router = $router;
    }

    /**
     * @return \Shopsys\HttpSmokeTesting\RouteInfo[]
     */
    public function getAllRouteInfo()
    {
        $allRouteInfo = [];
        $annotationReader = new AnnotationReader();
        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            $annotations = [];

            if ($route->hasDefault('_controller')) {
                try {
                    $reflectionMethod = new \ReflectionMethod($route->getDefault('_controller'));

                    foreach ($annotationReader->getMethodAnnotations($reflectionMethod) as $annotation) {
                        if ($annotation instanceof DataSet || $annotation instanceof Skipped) {
                            $annotations[] = $annotation;
                        }
                    }
                } catch (\ReflectionException $exception) {
                    // Just could not parse the reflection. Do noting.
                }
            }

            $allRouteInfo[] = new RouteInfo($routeName, $route, $annotations);
        }

        return $allRouteInfo;
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

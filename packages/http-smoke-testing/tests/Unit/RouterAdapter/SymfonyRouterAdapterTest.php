<?php

namespace Tests\HttpSmokeTesting\Unit\RouterAdapter;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Shopsys\HttpSmokeTesting\Annotation\DataSet;
use Shopsys\HttpSmokeTesting\Annotation\Parameter;
use Shopsys\HttpSmokeTesting\Annotation\Skipped;
use Shopsys\HttpSmokeTesting\RouteInfo;
use Shopsys\HttpSmokeTesting\RouterAdapter\SymfonyRouterAdapter;
use Shopsys\HttpSmokeTesting\Test\TestController;
use Symfony\Bundle\FrameworkBundle\Routing\AnnotatedRouteControllerLoader;
use Symfony\Component\Routing\Router;

class SymfonyRouterAdapterTest extends TestCase
{
    public function testGetAllRouteInfoExtractsInformationFromRouteCollection()
    {
        $router = new Router(
            new AnnotatedRouteControllerLoader(new AnnotationReader()),
            TestController::class
        );

        $adapter = new SymfonyRouterAdapter($router);

        $routeInfos = $adapter->getAllRouteInfo();

        self::assertCount(3, $routeInfos);

        self::assertInstanceOf(RouteInfo::class, $routeInfos[0]);
        self::assertSame('/hello/{name}', $routeInfos[0]->getRoutePath());
        self::assertCount(2, $routeInfos[0]->getAnnotations());
        self::assertInstanceOf(DataSet::class, $routeInfos[0]->getAnnotations()[0]);
        self::assertInstanceOf(DataSet::class, $routeInfos[0]->getAnnotations()[1]);
        self::assertSame(404, $routeInfos[0]->getAnnotations()[1]->statusCode);
        self::assertCount(1, $routeInfos[0]->getAnnotations()[1]->parameters);
        self::assertInstanceOf(Parameter::class, $routeInfos[0]->getAnnotations()[1]->parameters[0]);

        self::assertInstanceOf(RouteInfo::class, $routeInfos[1]);
        self::assertSame('/test', $routeInfos[1]->getRoutePath());
        self::assertCount(1, $routeInfos[1]->getAnnotations());
        self::assertInstanceOf(DataSet::class, $routeInfos[1]->getAnnotations()[0]);
        self::assertInstanceOf(Parameter::class, $routeInfos[1]->getAnnotations()[0]->parameters[0]);
        self::assertSame('myName', $routeInfos[1]->getAnnotations()[0]->parameters[0]->name);
        self::assertSame('Batman', $routeInfos[1]->getAnnotations()[0]->parameters[0]->value);

        self::assertInstanceOf(RouteInfo::class, $routeInfos[2]);
        self::assertSame('/untested', $routeInfos[2]->getRoutePath());
        self::assertCount(1, $routeInfos[2]->getAnnotations());
        self::assertInstanceOf(Skipped::class, $routeInfos[2]->getAnnotations()[0]);
    }
}

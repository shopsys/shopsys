<?php

namespace Tests\HttpSmokeTesting\Unit\RouterAdapter;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Shopsys\HttpSmokeTesting\Annotation\DataSet;
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

        self::assertCount(2, $routeInfos);

        self::assertInstanceOf(RouteInfo::class, $routeInfos[0]);
        self::assertSame('/hello/{name}', $routeInfos[0]->getRoutePath());
        self::assertCount(2, $routeInfos[0]->getAnnotations());
        self::assertInstanceOf(DataSet::class, $routeInfos[0]->getAnnotations()[0]);
        self::assertInstanceOf(DataSet::class, $routeInfos[0]->getAnnotations()[1]);
        self::assertSame(404, $routeInfos[0]->getAnnotations()[1]->statusCode);
        self::assertCount(1, $routeInfos[0]->getAnnotations()[1]->parameters);

        self::assertInstanceOf(RouteInfo::class, $routeInfos[1]);
        self::assertSame('/untested', $routeInfos[1]->getRoutePath());
        self::assertCount(1, $routeInfos[1]->getAnnotations());
        self::assertInstanceOf(Skipped::class, $routeInfos[1]->getAnnotations()[0]);
    }
}

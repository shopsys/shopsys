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

        $route1 = $routeInfos[0];
        self::assertInstanceOf(RouteInfo::class, $route1);
        self::assertSame('/hello/{name}', $route1->getRoutePath());
        self::assertCount(2, $route1->getAnnotations());

        $route1DataSet1 = $route1->getAnnotations()[0];
        $route1DataSet2 = $route1->getAnnotations()[1];
        self::assertInstanceOf(DataSet::class, $route1DataSet1);
        self::assertInstanceOf(DataSet::class, $route1DataSet2);
        self::assertSame(404, $route1DataSet2->statusCode);
        self::assertCount(1, $route1DataSet2->parameters);
        self::assertInstanceOf(Parameter::class, $route1DataSet2->parameters[0]);

        $route2 = $routeInfos[1];
        self::assertInstanceOf(RouteInfo::class, $route2);
        self::assertSame('/test', $route2->getRoutePath());
        self::assertCount(1, $route2->getAnnotations());

        $route2DataSet1 = $route2->getAnnotations()[0];
        self::assertInstanceOf(DataSet::class, $route2DataSet1);

        self::assertCount(1, $route2DataSet1->parameters);
        $route2DataSetParameter = $route2DataSet1->parameters[0];
        self::assertInstanceOf(Parameter::class, $route2DataSetParameter);
        self::assertSame('myName', $route2DataSetParameter->name);
        self::assertSame('Batman', $route2DataSetParameter->value);

        $route3 = $routeInfos[2];
        self::assertInstanceOf(RouteInfo::class, $route3);
        self::assertSame('/untested', $route3->getRoutePath());
        self::assertCount(1, $route3->getAnnotations());
        self::assertInstanceOf(Skipped::class, $route3->getAnnotations()[0]);
    }
}

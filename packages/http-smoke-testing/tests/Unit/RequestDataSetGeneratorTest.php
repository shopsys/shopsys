<?php

namespace Tests\HttpSmokeTesting\Unit;

use PHPUnit\Framework\TestCase;
use Shopsys\HttpSmokeTesting\RequestDataSetGenerator;
use Shopsys\HttpSmokeTesting\RouteInfo;
use Symfony\Component\Routing\Route;

class RequestDataSetGeneratorTest extends TestCase
{
    public function testGeneratorGeneratesExactlyOneRequestDataSet(): void
    {
        $requestDataSetGenerator = $this->createRequestDataSetGenerator('test_route_path', 'test_route_name');

        $requestDataSets = $requestDataSetGenerator->generateRequestDataSets();

        self::assertCount(1, $requestDataSets);
    }

    public function testGeneratorCanAddExtraRequestDataSet(): void
    {
        $requestDataSetGenerator = $this->createRequestDataSetGenerator('test_route_path', 'test_route_name');

        $requestDataSetGenerator->addExtraRequestDataSet();
        $requestDataSetGenerator->addExtraRequestDataSet();
        $requestDataSets = $requestDataSetGenerator->generateRequestDataSets();

        self::assertCount(3, $requestDataSets);
    }

    public function testGeneratorGeneratesUniqueInstancesOfEqualRequestDataSet(): void
    {
        $requestDataSetGenerator = $this->createRequestDataSetGenerator('test_route_path', 'test_route_name');

        $firstRequestDataSets = $requestDataSetGenerator->generateRequestDataSets();
        $secondRequestDataSets = $requestDataSetGenerator->generateRequestDataSets();

        self::assertEquals($firstRequestDataSets[0], $secondRequestDataSets[0]);
        self::assertNotSame($firstRequestDataSets[0], $secondRequestDataSets[0]);
    }

    private function createRequestDataSetGenerator(string $routePath, string $routeName): \Shopsys\HttpSmokeTesting\RequestDataSetGenerator
    {
        $route = new Route($routePath);
        $routeInfo = new RouteInfo($routeName, $route);
        $requestDataSetGenerator = new RequestDataSetGenerator($routeInfo);

        return $requestDataSetGenerator;
    }
}

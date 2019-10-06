<?php

namespace Tests\HttpSmokeTesting\Unit;

use PHPUnit\Framework\TestCase;
use Shopsys\HttpSmokeTesting\Annotation\DataSet;
use Shopsys\HttpSmokeTesting\Annotation\Parameter;
use Shopsys\HttpSmokeTesting\RequestDataSetGenerator;
use Shopsys\HttpSmokeTesting\RouteInfo;
use Symfony\Component\Routing\Route;

class RequestDataSetGeneratorTest extends TestCase
{
    public function testGeneratorGeneratesExactlyOneRequestDataSet()
    {
        $requestDataSetGenerator = $this->createRequestDataSetGenerator('test_route_path', 'test_route_name');

        $requestDataSets = $requestDataSetGenerator->generateRequestDataSets();

        self::assertCount(1, $requestDataSets);
    }

    public function testGeneratorCanAddExtraRequestDataSet()
    {
        $requestDataSetGenerator = $this->createRequestDataSetGenerator('test_route_path', 'test_route_name');

        $requestDataSetGenerator->addExtraRequestDataSet();
        $requestDataSetGenerator->addExtraRequestDataSet();
        $requestDataSets = $requestDataSetGenerator->generateRequestDataSets();

        self::assertCount(3, $requestDataSets);
    }

    public function testGeneratorGeneratesUniqueInstancesOfEqualRequestDataSet()
    {
        $requestDataSetGenerator = $this->createRequestDataSetGenerator('test_route_path', 'test_route_name');

        $firstRequestDataSets = $requestDataSetGenerator->generateRequestDataSets();
        $secondRequestDataSets = $requestDataSetGenerator->generateRequestDataSets();

        self::assertEquals($firstRequestDataSets[0], $secondRequestDataSets[0]);
        self::assertNotSame($firstRequestDataSets[0], $secondRequestDataSets[0]);
    }

    /**
     * @param string $routePath
     * @param string $routeName
     * @param array $annotations
     * @return \Shopsys\HttpSmokeTesting\RequestDataSetGenerator
     */
    private function createRequestDataSetGenerator($routePath, $routeName, array $annotations = [])
    {
        $route = new Route($routePath);
        $routeInfo = new RouteInfo($routeName, $route, $annotations);
        $requestDataSetGenerator = new RequestDataSetGenerator($routeInfo);

        return $requestDataSetGenerator;
    }

    public function testGeneratorGeneratesRequestDataSetsFromDataSetAnnotations()
    {
        $parameters = [
            [new Parameter()],
            [new Parameter()],
        ];
        $annotations = [
            new DataSet(),
            new DataSet(),
        ];

        $parameters[0][0]->name = 'name';
        $parameters[0][0]->value = 'Batman';
        $parameters[1][0]->name = 'name';
        $parameters[1][0]->value = 'World';

        $annotations[0]->parameters = $parameters[0];
        $annotations[1]->parameters = $parameters[1];
        $annotations[1]->statusCode = 404;

        $requestDataSetGenerator = $this->createRequestDataSetGenerator(
            'test_route_path',
            'test_route_name',
            $annotations
        );

        $requestDataSets = $requestDataSetGenerator->generateRequestDataSets();

        self::assertCount(2, $requestDataSets);

        self::assertEquals(['name' => 'Batman'], $requestDataSets[0]->getParameters());
        self::assertEquals(['name' => 'World'], $requestDataSets[1]->getParameters());
        self::assertSame(404, $requestDataSets[1]->getExpectedStatusCode());
    }
}

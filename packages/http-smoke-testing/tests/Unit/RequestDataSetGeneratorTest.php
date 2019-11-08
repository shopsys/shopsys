<?php

namespace Tests\HttpSmokeTesting\Unit;

use PHPUnit\Framework\TestCase;
use Shopsys\HttpSmokeTesting\Annotation\DataSet;
use Shopsys\HttpSmokeTesting\Annotation\Parameter;
use Shopsys\HttpSmokeTesting\RequestDataSetGeneratorFactory;
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
        $requestDataSetGeneratorFactory = new RequestDataSetGeneratorFactory();

        return $requestDataSetGeneratorFactory->create($routeInfo);
    }

    public function testGeneratorGeneratesRequestDataSetsFromDataSetAnnotations()
    {
        $parameter1 = new Parameter();
        $parameter1->name = 'name';
        $parameter1->value = 'Batman';

        $parameter2 = new Parameter();
        $parameter2->name = 'name';
        $parameter2->value = 'World';

        $dataSet1 = new DataSet();
        $dataSet1->parameters = [$parameter1];

        $dataSet2 = new DataSet();
        $dataSet2->parameters = [$parameter2];
        $dataSet2->statusCode = 404;

        $annotations = [
            $dataSet1,
            $dataSet2,
        ];

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

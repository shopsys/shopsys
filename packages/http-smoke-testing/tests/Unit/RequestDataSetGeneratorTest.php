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

    /**
     * @param DataSet $dataSet
     * @param int $statusCode
     * @param array $parameters
     * @dataProvider getDataSets
     */
    public function testGeneratorGenerateRequestDataSetFromDataSerAnnotation(
        DataSet $dataSet,
        int $statusCode,
        array $parameters
    ) {
        $requestDataSetGenerator = $this->createRequestDataSetGenerator(
            'test_route_path',
            'test_route_name',
            [$dataSet]
        );
        $requestDataSets = $requestDataSetGenerator->generateRequestDataSets();

        self::assertCount(1, $requestDataSets);
        self::assertSame($statusCode, $requestDataSets[0]->getExpectedStatusCode());
        self::assertEquals($parameters, $requestDataSets[0]->getParameters());
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

    public function getDataSets(): array
    {
        $parameter1 = new Parameter();
        $parameter1->name = 'name';
        $parameter1->value = 'Batman';

        $parameter2 = new Parameter();
        $parameter2->name = 'foo';
        $parameter2->value = 'Bar';

        $dataSet1 = new DataSet();
        $dataSet1->parameters = [$parameter1];

        $dataSet2 = new DataSet();
        $dataSet2->parameters = [$parameter1, $parameter2];
        $dataSet2->statusCode = 404;

        $dataSet3 = new DataSet();

        $dataSet4 = new DataSet();
        $dataSet4->statusCode = 302;

        $dataSet5 = new DataSet();
        $dataSet5->statusCode = 500;
        $dataSet5->parameters = [];

        return [
            [$dataSet1, 200, ['name' => 'Batman']],
            [$dataSet2, 404, ['name' => 'Batman', 'foo' => 'Bar']],
            [$dataSet3, 200, []],
            [$dataSet4, 302, []],
            [$dataSet5, 500, []],
        ];
    }
}

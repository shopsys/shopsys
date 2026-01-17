<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Router\AdministrationRouter;
use Shopsys\HttpSmokeTesting\RouterAdapter\SymfonyRouterAdapter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

abstract class HttpSmokeTestCase extends KernelTestCase
{
    protected const APP_ENV = 'test';
    protected const APP_DEBUG = false;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before data provider is executed and before each test.
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        static::boot();
    }

    protected static function boot(): void
    {
        static::bootKernel([
            'environment' => static::APP_ENV,
            'debug' => static::APP_DEBUG,
        ]);
    }

    /**
     * The main test method for smoke testing of all routes in your application.
     *
     * You must configure the provided RequestDataSets by implementing customizeRouteConfigs method.
     * If you need custom behavior for creating or handling requests in your application you should override the
     * createRequest or handleRequest method.
     */
    #[DataProvider('httpResponseTestDataProvider')]
    final public function testHttpResponse(RequestDataSet $requestDataSet)
    {
        if ($requestDataSet->isSkipped()) {
            $message = sprintf('Test for route "%s" was skipped.', $requestDataSet->getRouteName());
            $this->markTestSkipped($this->getMessageWithDebugNotes($requestDataSet, $message));
        }

        $request = $this->createRequest($requestDataSet);

        $requestDataSet->executeCallsDuringTestExecution(static::$kernel->getContainer());

        $request->query->add($requestDataSet->getParameters());

        $response = $this->handleRequest($request);

        $this->assertResponse($response, $requestDataSet);
    }

    /**
     * Data provider for the testHttpResponse method.
     *
     * This method gets all RouteInfo objects provided by RouterAdapter. It then passes them into
     * customizeRouteConfigs() method for customization and returns the resulting RequestDataSet objects.
     *
     * @return \Shopsys\HttpSmokeTesting\RequestDataSet[][]
     */
    public static function httpResponseTestDataProvider()
    {
        static::boot();

        $requestDataSetGeneratorFactory = new RequestDataSetGeneratorFactory();
        /** @var \Shopsys\HttpSmokeTesting\RequestDataSetGenerator[] $requestDataSetGenerators */
        $requestDataSetGenerators = [];

        $allRouteInfo = static::getRouterAdapter()->getAllRouteInfo();

        foreach ($allRouteInfo as $routeInfo) {
            $requestDataSetGenerators[] = $requestDataSetGeneratorFactory->create($routeInfo);
        }

        $routeConfigCustomizer = new RouteConfigCustomizer($requestDataSetGenerators);

        static::customizeRouteConfigs($routeConfigCustomizer);

        $requestDataSets = [];

        foreach ($requestDataSetGenerators as $requestDataSetGenerator) {
            $requestDataSets = array_merge($requestDataSets, $requestDataSetGenerator->generateRequestDataSets());
        }

        return array_map(
            function (RequestDataSet $requestDataSet) {
                return [$requestDataSet];
            },
            $requestDataSets,
        );
    }

    /**
     * @return \Shopsys\HttpSmokeTesting\RouterAdapter\RouterAdapterInterface
     */
    protected static function getRouterAdapter()
    {
        $router = static::$kernel->getContainer()->get('test.service_container')->get(AdministrationRouter::class);

        return new SymfonyRouterAdapter($router);
    }

    /**
     * This method must be implemented to customize and configure the test cases for individual routes
     */
    abstract protected static function customizeRouteConfigs(RouteConfigCustomizer $routeConfigCustomizer);

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected static function createRequest(RequestDataSet $requestDataSet)
    {
        $uri = static::getRouterAdapter()->generateUri($requestDataSet);

        $request = Request::create($uri);
        /** @var \Symfony\Component\HttpFoundation\Session\SessionFactory $sessionFactory */
        $sessionFactory = static::$kernel->getContainer()->get('test.service_container')->get('session.factory');
        /** @var \Symfony\Component\HttpFoundation\RequestStack $requestStack */
        $requestStack = static::$kernel->getContainer()->get(RequestStack::class);

        $session = $sessionFactory->createSession();
        $request->setSession($session);

        $requestDataSet->getAuth()
            ->authenticateRequest($request);

        $requestStack->push($request);

        return $request;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleRequest(Request $request)
    {
        return static::$kernel->handle($request);
    }

    protected function assertResponse(Response $response, RequestDataSet $requestDataSet)
    {
        $failMessage = sprintf(
            'Failed asserting that status code %d for route "%s" is identical to expected %d',
            $response->getStatusCode(),
            $requestDataSet->getRouteName(),
            $requestDataSet->getExpectedStatusCode(),
        );
        $this->assertSame(
            $requestDataSet->getExpectedStatusCode(),
            $response->getStatusCode(),
            $this->getMessageWithDebugNotes($requestDataSet, $failMessage),
        );
    }

    /**
     * @param string $message
     * @return string
     */
    protected function getMessageWithDebugNotes(RequestDataSet $requestDataSet, $message)
    {
        if (count($requestDataSet->getDebugNotes()) > 0) {
            $indentedDebugNotes = array_map(function ($debugNote) {
                return "\n" . '  - ' . $debugNote;
            }, $requestDataSet->getDebugNotes());
            $message .= "\n" . 'Notes for this data set:' . implode($indentedDebugNotes);
        }

        return $message;
    }
}

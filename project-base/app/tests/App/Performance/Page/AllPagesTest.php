<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Router\AdministrationRouter;
use Shopsys\HttpSmokeTesting\RequestDataSet;
use Shopsys\HttpSmokeTesting\RequestDataSetGeneratorFactory;
use Shopsys\HttpSmokeTesting\RouteConfig;
use Shopsys\HttpSmokeTesting\RouteConfigCustomizer;
use Shopsys\HttpSmokeTesting\RouteInfo;
use Shopsys\HttpSmokeTesting\RouterAdapter\SymfonyRouterAdapter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\App\Performance\JmeterCsvReporter;
use Tests\App\Smoke\Http\RouteConfigCustomization;

class AllPagesTest extends KernelTestCase
{
    protected const PASSES = 5;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        static::bootKernel([
            'environment' => EnvironmentType::TEST,
            'debug' => EnvironmentType::isDebug(EnvironmentType::TEST),
        ]);

        static::getContainer()->get(Domain::class)
            ->switchDomainById(Domain::FIRST_DOMAIN_ID);
    }

    #[Group('warmup')]
    public function testAdminPagesWarmup(): void
    {
        $this->doWarmupPagesWithProgress(
            $this->getRequestDataSets('~^admin_~'),
        );
    }

    #[Group('warmup')]
    public function testFrontPagesWarmup(): void
    {
        $this->doWarmupPagesWithProgress(
            $this->getRequestDataSets('~^front~'),
        );
    }

    public function testAdminPages(): void
    {
        $this->doTestPagesWithProgress(
            $this->getRequestDataSets('~^admin_~'),
            static::getContainer()->getParameter('kernel.project_dir') . '/build/stats/performance-tests-admin.csv',
        );
    }

    public function testFrontPages(): void
    {
        $this->doTestPagesWithProgress(
            $this->getRequestDataSets('~^front~'),
            static::getContainer()->getParameter('kernel.project_dir') . '/build/stats/performance-tests-front.csv',
        );
    }

    /**
     * @return \Shopsys\HttpSmokeTesting\RequestDataSet[]
     */
    private function getRequestDataSets(string $routeNamePattern): array
    {
        $requestDataSetGenerators = [];
        $allRouteInfo = $this->getRouterAdapter()->getAllRouteInfo();
        $requestDataSetGeneratorFactory = new RequestDataSetGeneratorFactory();

        foreach ($allRouteInfo as $routeInfo) {
            $requestDataSetGenerators[] = $requestDataSetGeneratorFactory->create($routeInfo);
        }

        $routeConfigCustomizer = new RouteConfigCustomizer($requestDataSetGenerators);
        $routeConfigCustomization = new RouteConfigCustomization(static::getContainer());
        $routeConfigCustomization->customizeRouteConfigs($routeConfigCustomizer);

        $routeConfigCustomizer->customize(function (RouteConfig $config, RouteInfo $info) use ($routeNamePattern): void {
            if (!preg_match($routeNamePattern, $info->getRouteName())) {
                $config->skipRoute('Route name does not match pattern "' . $routeNamePattern . '".');
            }
        });

        $allRequestDataSets = [];

        foreach ($requestDataSetGenerators as $requestDataSetGenerator) {
            $requestDataSets = $requestDataSetGenerator->generateRequestDataSets();

            $nonSkippedRequestDataSets = array_filter($requestDataSets, function (RequestDataSet $requestDataSet) {
                return !$requestDataSet->isSkipped();
            });

            $allRequestDataSets = array_merge($allRequestDataSets, $nonSkippedRequestDataSets);
        }

        return $allRequestDataSets;
    }

    /**
     * @param \Shopsys\HttpSmokeTesting\RequestDataSet[] $requestDataSets
     */
    private function doWarmupPagesWithProgress(array $requestDataSets): void
    {
        $consoleOutput = new ConsoleOutput();
        $consoleOutput->writeln('');

        $requestDataSetCount = count($requestDataSets);
        $requestDataSetIndex = 0;

        foreach ($requestDataSets as $requestDataSet) {
            $requestDataSetIndex++;

            $progressLine = sprintf(
                'Warmup: %3d%% (%s)',
                round($requestDataSetIndex / $requestDataSetCount * 100),
                $requestDataSet->getRouteName(),
            );
            $consoleOutput->write(str_pad($progressLine, 80) . "\r");

            $this->doTestRequestDataSet($requestDataSet);
        }
    }

    /**
     * @param \Shopsys\HttpSmokeTesting\RequestDataSet[] $requestDataSets
     */
    private function doTestPagesWithProgress(array $requestDataSets, string $jmeterOutputFilename): void
    {
        $consoleOutput = new ConsoleOutput();
        $consoleOutput->writeln('');

        $performanceTestSamples = [];

        $requestDataSetCount = count($requestDataSets);

        for ($pass = 1; $pass <= self::PASSES; $pass++) {
            $requestDataSetIndex = 0;

            foreach ($requestDataSets as $requestDataSet) {
                $requestDataSetIndex++;

                $progressLine = sprintf(
                    '%s: %3d%% (%s)',
                    'Pass ' . $pass . '/' . self::PASSES,
                    round($requestDataSetIndex / $requestDataSetCount * 100),
                    $requestDataSet->getRouteName(),
                );
                $consoleOutput->write(str_pad($progressLine, 80) . "\r");

                $performanceTestSamples[] = $this->doTestRequestDataSet($requestDataSet);
            }
        }

        $performanceTestSamplesAggregatedByUrl = $this->aggregatePerformanceTestSamplesByUrl($performanceTestSamples);
        $this->exportJmeterCsvReport($performanceTestSamples, $jmeterOutputFilename);
        $this->printPerformanceTestsSummary($performanceTestSamplesAggregatedByUrl, $consoleOutput);

        $this->doAssert($performanceTestSamplesAggregatedByUrl);
    }

    private function doTestRequestDataSet(RequestDataSet $requestDataSet): PerformanceTestSample
    {
        $this->setUp();

        $uri = $this->getRouterAdapter()->generateUri($requestDataSet);

        $request = Request::create($uri);

        $sessionFactory = static::getContainer()->get('test.service_container')->get('session.factory');
        $session = $sessionFactory->createSession();
        $request->setSession($session);

        $requestDataSet->getAuth()->authenticateRequest($request);

        $requestStack = static::getContainer()->get(RequestStack::class);
        $requestStack->push($request);

        $requestDataSet->executeCallsDuringTestExecution(static::getContainer());

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $queryCounter = $this->injectQueryCounter($entityManager);
        $startTime = microtime(true);
        $entityManager->beginTransaction();
        $response = static::$kernel->handle($request);
        $queryCount = $queryCounter->getQueryCount();
        $entityManager->rollback();
        $endTime = microtime(true);

        $statusCode = $response->getStatusCode();

        return new PerformanceTestSample(
            $requestDataSet->getRouteName(),
            $uri,
            ($endTime - $startTime) * 1000,
            $queryCount,
            $statusCode,
            $statusCode === $requestDataSet->getExpectedStatusCode(),
        );
    }

    /**
     * @param \Tests\App\Performance\Page\PerformanceTestSample[] $performanceTestSamples
     */
    private function doAssert(
        array $performanceTestSamples,
    ): void {
        $performanceTestSampleQualifier = $this->createPerformanceTestSampleQualifier();

        $overallStatus = $performanceTestSampleQualifier->getOverallStatus($performanceTestSamples);

        switch ($overallStatus) {
            case PerformanceTestSampleQualifier::STATUS_OK:
            case PerformanceTestSampleQualifier::STATUS_WARNING:
                return;
            case PerformanceTestSampleQualifier::STATUS_CRITICAL:
            default:
                $this->fail('Values are above critical threshold');
        }
    }

    /**
     * @param \Tests\App\Performance\Page\PerformanceTestSample[] $performanceTestSamples
     */
    private function exportJmeterCsvReport(array $performanceTestSamples, string $jmeterOutputFilename): void
    {
        $jmeterCsvReporter = new JmeterCsvReporter();
        $performanceResultsCsvExporter = new PerformanceResultsCsvExporter($jmeterCsvReporter);

        $performanceResultsCsvExporter->exportJmeterCsvReport($performanceTestSamples, $jmeterOutputFilename);
    }

    /**
     * @param \Tests\App\Performance\Page\PerformanceTestSample[] $performanceTestSamples
     * @return \Tests\App\Performance\Page\PerformanceTestSample[]
     */
    private function aggregatePerformanceTestSamplesByUrl(array $performanceTestSamples): array
    {
        $performanceTestSamplesAggregator = new PerformanceTestSamplesAggregator();

        return $performanceTestSamplesAggregator->getPerformanceTestSamplesAggregatedByUrl($performanceTestSamples);
    }

    /**
     * @param \Tests\App\Performance\Page\PerformanceTestSample[] $performanceTestSamples
     */
    private function printPerformanceTestsSummary(array $performanceTestSamples, ConsoleOutput $consoleOutput): void
    {
        $performanceTestSampleQualifier = $this->createPerformanceTestSampleQualifier();
        $performanceTestSummaryPrinter = new PerformanceTestSummaryPrinter($performanceTestSampleQualifier);

        $performanceTestSummaryPrinter->printSummary($performanceTestSamples, $consoleOutput);
    }

    private function getRouterAdapter(): SymfonyRouterAdapter
    {
        $router = static::getContainer()->get('test.service_container')->get(AdministrationRouter::class);

        return new SymfonyRouterAdapter($router);
    }

    private function injectQueryCounter(EntityManagerInterface $entityManager): QueryCountingMiddleware
    {
        $connection = $entityManager->getConnection();

        $queryCountingMiddleware = new QueryCountingMiddleware();

        $connectionReflection = new ReflectionClass($connection);
        $driverProperty = $connectionReflection->getProperty('_driver');
        $currentDriver = $driverProperty->getValue($connection);

        $wrappedDriver = $queryCountingMiddleware->wrap($currentDriver);

        $driverProperty->setValue($connection, $wrappedDriver);

        $connection->close();

        return $queryCountingMiddleware;
    }

    private function createPerformanceTestSampleQualifier(): PerformanceTestSampleQualifier
    {
        $container = static::getContainer();

        return new PerformanceTestSampleQualifier(
            $container->getParameter('shopsys.performance_test.page.duration_milliseconds.warning'),
            $container->getParameter('shopsys.performance_test.page.duration_milliseconds.critical'),
            $container->getParameter('shopsys.performance_test.page.query_count.warning'),
            $container->getParameter('shopsys.performance_test.page.query_count.critical'),
        );
    }
}

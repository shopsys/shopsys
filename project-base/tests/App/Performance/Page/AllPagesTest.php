<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

use Doctrine\DBAL\Logging\LoggerChain;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\HttpSmokeTesting\RequestDataSet;
use Shopsys\HttpSmokeTesting\RequestDataSetGeneratorFactory;
use Shopsys\HttpSmokeTesting\RouteConfig;
use Shopsys\HttpSmokeTesting\RouteConfigCustomizer;
use Shopsys\HttpSmokeTesting\RouteInfo;
use Shopsys\HttpSmokeTesting\RouterAdapter\SymfonyRouterAdapter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\Performance\JmeterCsvReporter;
use Tests\App\Smoke\Http\RouteConfigCustomization;

class AllPagesTest extends KernelTestCase
{
    protected const PASSES = 5;

    protected function setUp(): void
    {
        parent::setUp();

        static::bootKernel([
            'environment' => EnvironmentType::TEST,
            'debug' => EnvironmentType::isDebug(EnvironmentType::TEST),
        ]);

        static::$container->get(Domain::class)
            ->switchDomainById(Domain::FIRST_DOMAIN_ID);
    }

    /**
     * @group warmup
     */
    public function testAdminPagesWarmup()
    {
        $this->doWarmupPagesWithProgress(
            $this->getRequestDataSets('~^admin_~')
        );
    }

    /**
     * @group warmup
     */
    public function testFrontPagesWarmup()
    {
        $this->doWarmupPagesWithProgress(
            $this->getRequestDataSets('~^front~')
        );
    }

    public function testAdminPages()
    {
        $this->doTestPagesWithProgress(
            $this->getRequestDataSets('~^admin_~'),
            static::$container->getParameter('shopsys.root_dir') . '/build/stats/performance-tests-admin.csv'
        );
    }

    public function testFrontPages()
    {
        $this->doTestPagesWithProgress(
            $this->getRequestDataSets('~^front~'),
            static::$container->getParameter('shopsys.root_dir') . '/build/stats/performance-tests-front.csv'
        );
    }

    /**
     * @param string $routeNamePattern
     * @return \Shopsys\HttpSmokeTesting\RequestDataSet[]
     */
    private function getRequestDataSets($routeNamePattern)
    {
        $requestDataSetGenerators = [];
        $allRouteInfo = $this->getRouterAdapter()->getAllRouteInfo();
        $requestDataSetGeneratorFactory = new RequestDataSetGeneratorFactory();
        foreach ($allRouteInfo as $routeInfo) {
            $requestDataSetGenerators[] = $requestDataSetGeneratorFactory->create($routeInfo);
        }

        $routeConfigCustomizer = new RouteConfigCustomizer($requestDataSetGenerators);
        $routeConfigCustomization = new RouteConfigCustomization(static::$container);
        $routeConfigCustomization->customizeRouteConfigs($routeConfigCustomizer);

        $routeConfigCustomizer->customize(function (RouteConfig $config, RouteInfo $info) use ($routeNamePattern) {
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
    private function doWarmupPagesWithProgress(array $requestDataSets)
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
                $requestDataSet->getRouteName()
            );
            $consoleOutput->write(str_pad($progressLine, 80) . "\r");

            $this->doTestRequestDataSet($requestDataSet);
        }
    }

    /**
     * @param \Shopsys\HttpSmokeTesting\RequestDataSet[] $requestDataSets
     * @param string $jmeterOutputFilename
     */
    private function doTestPagesWithProgress(array $requestDataSets, $jmeterOutputFilename)
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
                    $requestDataSet->getRouteName()
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

    /**
     * @param \Shopsys\HttpSmokeTesting\RequestDataSet $requestDataSet
     * @return \Tests\App\Performance\Page\PerformanceTestSample
     */
    private function doTestRequestDataSet(RequestDataSet $requestDataSet)
    {
        $this->setUp();

        $requestDataSet->executeCallsDuringTestExecution(static::$container);

        $uri = $this->getRouterAdapter()->generateUri($requestDataSet);

        $request = Request::create($uri);
        $requestDataSet->getAuth()->authenticateRequest($request);

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = static::$container->get('doctrine.orm.entity_manager');

        $startTime = microtime(true);
        $entityManager->beginTransaction();
        $queryCounter = $this->injectQueryCounter($entityManager);
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
            $statusCode === $requestDataSet->getExpectedStatusCode()
        );
    }

    /**
     * @param \Tests\App\Performance\Page\PerformanceTestSample[] $performanceTestSamples
     */
    private function doAssert(
        array $performanceTestSamples
    ) {
        $performanceTestSampleQualifier = $this->createPerformanceTestSampleQualifier();

        $overallStatus = $performanceTestSampleQualifier->getOverallStatus($performanceTestSamples);

        switch ($overallStatus) {
            case PerformanceTestSampleQualifier::STATUS_OK:
            case PerformanceTestSampleQualifier::STATUS_WARNING:
                $this->assertTrue(true);
                return;
            case PerformanceTestSampleQualifier::STATUS_CRITICAL:
            default:
                $this->fail('Values are above critical threshold');
        }
    }

    /**
     * @param \Tests\App\Performance\Page\PerformanceTestSample[] $performanceTestSamples
     * @param string $jmeterOutputFilename
     */
    private function exportJmeterCsvReport(array $performanceTestSamples, $jmeterOutputFilename)
    {
        $jmeterCsvReporter = new JmeterCsvReporter();
        $performanceResultsCsvExporter = new PerformanceResultsCsvExporter($jmeterCsvReporter);

        $performanceResultsCsvExporter->exportJmeterCsvReport($performanceTestSamples, $jmeterOutputFilename);
    }

    /**
     * @param \Tests\App\Performance\Page\PerformanceTestSample[] $performanceTestSamples
     * @return \Tests\App\Performance\Page\PerformanceTestSample[]
     */
    private function aggregatePerformanceTestSamplesByUrl(array $performanceTestSamples)
    {
        $performanceTestSamplesAggregator = new PerformanceTestSamplesAggregator();

        return $performanceTestSamplesAggregator->getPerformanceTestSamplesAggregatedByUrl($performanceTestSamples);
    }

    /**
     * @param \Tests\App\Performance\Page\PerformanceTestSample[] $performanceTestSamples
     * @param \Symfony\Component\Console\Output\ConsoleOutput $consoleOutput
     */
    private function printPerformanceTestsSummary(array $performanceTestSamples, ConsoleOutput $consoleOutput)
    {
        $performanceTestSampleQualifier = $this->createPerformanceTestSampleQualifier();
        $performanceTestSummaryPrinter = new PerformanceTestSummaryPrinter($performanceTestSampleQualifier);

        $performanceTestSummaryPrinter->printSummary($performanceTestSamples, $consoleOutput);
    }

    /**
     * @return \Shopsys\HttpSmokeTesting\RouterAdapter\SymfonyRouterAdapter
     */
    private function getRouterAdapter()
    {
        $router = static::$container->get('router');
        $routerAdapter = new SymfonyRouterAdapter($router);

        return $routerAdapter;
    }

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @return \Tests\App\Performance\Page\PerformanceTestSampleQueryCounter
     */
    private function injectQueryCounter(EntityManagerInterface $entityManager)
    {
        $connectionConfiguration = $entityManager->getConnection()->getConfiguration();
        $loggerChain = new LoggerChain();

        $currentLogger = $connectionConfiguration->getSQLLogger();
        if ($currentLogger !== null) {
            $loggerChain->addLogger($currentLogger);
        }

        $queryCounter = new PerformanceTestSampleQueryCounter();
        $loggerChain->addLogger($queryCounter);

        $connectionConfiguration->setSQLLogger($loggerChain);

        return $queryCounter;
    }

    /**
     * @return \Tests\App\Performance\Page\PerformanceTestSampleQualifier
     */
    private function createPerformanceTestSampleQualifier()
    {
        $container = static::$container;

        return new PerformanceTestSampleQualifier(
            $container->getParameter('shopsys.performance_test.page.duration_milliseconds.warning'),
            $container->getParameter('shopsys.performance_test.page.duration_milliseconds.critical'),
            $container->getParameter('shopsys.performance_test.page.query_count.warning'),
            $container->getParameter('shopsys.performance_test.page.query_count.critical')
        );
    }
}

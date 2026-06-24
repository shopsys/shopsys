<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronFilter;
use Shopsys\FrameworkBundle\Component\Cron\CronModule;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleRepository;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleRun;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleRunFactory;
use Shopsys\FrameworkBundle\Component\Cron\SentryCronMonitorFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Component\Clock\DatePoint;

class CronModuleFacadeTest extends TestCase
{
    private const string SERVICE_ID = 'App\Cron\FooCronModule';

    public function testMarkCronAsStartedReportsStartToSentryMonitor(): void
    {
        $cronModuleConfig = $this->createCronModuleConfig();

        $sentryCronMonitorFacadeMock = $this->createMock(SentryCronMonitorFacade::class);
        $sentryCronMonitorFacadeMock->expects($this->once())
            ->method('reportStart')
            ->with($cronModuleConfig);

        $this->createCronModuleFacade($sentryCronMonitorFacadeMock)->markCronAsStarted($cronModuleConfig);
    }

    public function testMarkCronAsEndedReportsSuccessToSentryMonitor(): void
    {
        $cronModuleConfig = $this->createCronModuleConfig();

        $sentryCronMonitorFacadeMock = $this->createMock(SentryCronMonitorFacade::class);
        $sentryCronMonitorFacadeMock->expects($this->once())
            ->method('reportSuccess')
            ->with($cronModuleConfig);

        $this->createCronModuleFacade($sentryCronMonitorFacadeMock)->markCronAsEnded($cronModuleConfig);
    }

    public function testMarkCronAsFailedReportsFailureToSentryMonitorEvenWithoutDatabaseConnection(): void
    {
        $cronModuleConfig = $this->createCronModuleConfig();

        $sentryCronMonitorFacadeMock = $this->createMock(SentryCronMonitorFacade::class);
        $sentryCronMonitorFacadeMock->expects($this->once())
            ->method('reportFailure')
            ->with($cronModuleConfig);

        $this->createCronModuleFacade($sentryCronMonitorFacadeMock)->markCronAsFailed($cronModuleConfig);
    }

    private function createCronModuleFacade(SentryCronMonitorFacade $sentryCronMonitorFacade): CronModuleFacade
    {
        $cronModuleRepositoryStub = $this->createStub(CronModuleRepository::class);
        $cronModuleRepositoryStub->method('getCronModuleByServiceId')
            ->willReturn(new CronModule(self::SERVICE_ID));

        $cronModuleRunFactoryStub = $this->createStub(CronModuleRunFactory::class);
        $cronModuleRunFactoryStub->method('createFromFinishedCronModule')
            ->willReturn($this->createStub(CronModuleRun::class));

        $connectionStub = $this->createStub(Connection::class);
        $connectionStub->method('isConnected')->willReturn(false);

        $entityManagerStub = $this->createStub(EntityManagerInterface::class);
        $entityManagerStub->method('getConnection')->willReturn($connectionStub);

        $clockStub = $this->createStub(ClockInterface::class);
        $clockStub->method('now')->willReturn(new DatePoint());

        return new CronModuleFacade(
            $entityManagerStub,
            $cronModuleRepositoryStub,
            $this->createStub(CronFilter::class),
            $cronModuleRunFactoryStub,
            $this->createStub(InMemoryCache::class),
            $clockStub,
            $sentryCronMonitorFacade,
        );
    }

    private function createCronModuleConfig(): CronModuleConfig
    {
        return new CronModuleConfig(
            $this->createStub(SimpleCronModuleInterface::class),
            self::SERVICE_ID,
            '* * * * *',
        );
    }
}

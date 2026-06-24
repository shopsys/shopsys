<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\SentryMonitorConfig;
use Shopsys\FrameworkBundle\Component\Cron\MonitorConfigFactory;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class SentryCronMonitorFacadeTest extends TestCase
{
    private const string SERVICE_ID = 'App\Cron\FooCronModule';
    private const string SLUG = 'foo-slug';

    public function testReportStartAndSuccessSendPairedCheckIns(): void
    {
        $facade = $this->createFacade();
        $cronModuleConfig = $this->createMonitoredCronModuleConfig();

        $facade->reportStart($cronModuleConfig);
        $facade->reportSuccess($cronModuleConfig);

        $this->assertCount(2, $facade->capturedCheckIns);
        $this->assertSame('in_progress', $facade->capturedCheckIns[0]['status']);
        $this->assertSame('ok', $facade->capturedCheckIns[1]['status']);
        $this->assertSame(self::SLUG, $facade->capturedCheckIns[0]['slug']);
        $this->assertSame(self::SLUG, $facade->capturedCheckIns[1]['slug']);
        $this->assertSame($facade->capturedCheckIns[0]['checkInId'], $facade->capturedCheckIns[1]['checkInId']);
    }

    public function testReportStartAndFailureSendPairedCheckIns(): void
    {
        $facade = $this->createFacade();
        $cronModuleConfig = $this->createMonitoredCronModuleConfig();

        $facade->reportStart($cronModuleConfig);
        $facade->reportFailure($cronModuleConfig);

        $this->assertCount(2, $facade->capturedCheckIns);
        $this->assertSame('in_progress', $facade->capturedCheckIns[0]['status']);
        $this->assertSame('error', $facade->capturedCheckIns[1]['status']);
        $this->assertSame($facade->capturedCheckIns[0]['checkInId'], $facade->capturedCheckIns[1]['checkInId']);
    }

    public function testEveryCheckInCarriesMonitorConfigSoSentryCanUpsertTheMonitor(): void
    {
        $facade = $this->createFacade();
        $cronModuleConfig = $this->createMonitoredCronModuleConfig();

        $facade->reportStart($cronModuleConfig);
        $facade->reportSuccess($cronModuleConfig);
        $facade->reportFailure($cronModuleConfig);
        $facade->reportDisabledRunAsHealthy($cronModuleConfig);

        foreach ($facade->capturedCheckIns as $capturedCheckIn) {
            $this->assertTrue($capturedCheckIn['hasMonitorConfig']);
        }
    }

    public function testReportSuccessWithoutPreviousStartUpsertsMonitorWithoutId(): void
    {
        $facade = $this->createFacade();

        $facade->reportSuccess($this->createMonitoredCronModuleConfig());

        $this->assertCount(1, $facade->capturedCheckIns);
        $this->assertSame('ok', $facade->capturedCheckIns[0]['status']);
        $this->assertNull($facade->capturedCheckIns[0]['checkInId']);
        $this->assertTrue($facade->capturedCheckIns[0]['hasMonitorConfig']);
    }

    public function testReportFailureWithoutPreviousStartUpsertsMonitorWithoutId(): void
    {
        $facade = $this->createFacade();

        $facade->reportFailure($this->createMonitoredCronModuleConfig());

        $this->assertCount(1, $facade->capturedCheckIns);
        $this->assertSame('error', $facade->capturedCheckIns[0]['status']);
        $this->assertNull($facade->capturedCheckIns[0]['checkInId']);
        $this->assertTrue($facade->capturedCheckIns[0]['hasMonitorConfig']);
    }

    public function testReportDisabledRunAsHealthySendsPairedCheckIns(): void
    {
        $facade = $this->createFacade();

        $facade->reportDisabledRunAsHealthy($this->createMonitoredCronModuleConfig());

        $this->assertCount(2, $facade->capturedCheckIns);
        $this->assertSame('in_progress', $facade->capturedCheckIns[0]['status']);
        $this->assertSame('ok', $facade->capturedCheckIns[1]['status']);
        $this->assertSame($facade->capturedCheckIns[0]['checkInId'], $facade->capturedCheckIns[1]['checkInId']);
    }

    public function testNoCheckInsWhenMonitoringDisabled(): void
    {
        $facade = $this->createFacade();
        $cronModuleConfig = $this->createUnmonitoredCronModuleConfig();

        $facade->reportStart($cronModuleConfig);
        $facade->reportSuccess($cronModuleConfig);
        $facade->reportFailure($cronModuleConfig);
        $facade->reportDisabledRunAsHealthy($cronModuleConfig);

        $this->assertSame([], $facade->capturedCheckIns);
    }

    public function testCheckInErrorIsSwallowedAndLogged(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())->method('error');

        $facade = $this->createFacade($loggerMock);
        $facade->throwOnCapture = new RuntimeException('Sentry is down');

        $facade->reportStart($this->createMonitoredCronModuleConfig());

        $this->assertSame([], $facade->capturedCheckIns);
    }

    private function createFacade(?LoggerInterface $logger = null): TestSentryCronMonitorFacade
    {
        return new TestSentryCronMonitorFacade(
            $logger ?? $this->createStub(LoggerInterface::class),
            new MonitorConfigFactory(),
        );
    }

    private function createMonitoredCronModuleConfig(): CronModuleConfig
    {
        return $this->createCronModuleConfig(new SentryMonitorConfig(self::SLUG));
    }

    private function createUnmonitoredCronModuleConfig(): CronModuleConfig
    {
        return $this->createCronModuleConfig(null);
    }

    private function createCronModuleConfig(?SentryMonitorConfig $sentryMonitorConfig): CronModuleConfig
    {
        return new CronModuleConfig(
            $this->createStub(SimpleCronModuleInterface::class),
            self::SERVICE_ID,
            '* * * * *',
            null,
            null,
            CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
            CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
            $sentryMonitorConfig,
        );
    }
}

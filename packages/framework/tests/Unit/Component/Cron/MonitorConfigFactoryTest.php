<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use PHPUnit\Framework\TestCase;
use Sentry\MonitorConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\SentryMonitorConfig;
use Shopsys\FrameworkBundle\Component\Cron\MonitorConfigFactory;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class MonitorConfigFactoryTest extends TestCase
{
    private const string SERVICE_ID = 'App\Cron\FooCronModule';
    private const string SLUG = 'foo-slug';

    public function testUsesExplicitMaxRuntimeAndThresholds(): void
    {
        $monitorConfig = $this->create(
            $this->createStub(SimpleCronModuleInterface::class),
            '0 4 * * *',
            CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
            CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
            new SentryMonitorConfig(self::SLUG, maxRuntime: 5, checkinMargin: null, failureThreshold: 3, recoveryThreshold: 2),
        );

        $this->assertSame('0 4 * * *', $monitorConfig->getSchedule()->getValue());
        $this->assertSame(5, $monitorConfig->getMaxRuntime());
        $this->assertSame(3, $monitorConfig->getFailureRecoveryThreshold());
        $this->assertSame(2, $monitorConfig->getRecoveryThreshold());
        $this->assertSame('Europe/Prague', $monitorConfig->getTimezone());
    }

    public function testFallsBackCheckinMarginToRunEveryMin(): void
    {
        $monitorConfig = $this->create(
            $this->createStub(SimpleCronModuleInterface::class),
            '* * * * *',
            10,
            CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
            new SentryMonitorConfig(self::SLUG, maxRuntime: 5),
        );

        $this->assertSame(10, $monitorConfig->getCheckinMargin());
    }

    public function testDisablesCheckinMarginWhenZero(): void
    {
        $monitorConfig = $this->create(
            $this->createStub(SimpleCronModuleInterface::class),
            '* * * * *',
            CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
            CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
            new SentryMonitorConfig(self::SLUG, checkinMargin: 0),
        );

        $this->assertNull($monitorConfig->getCheckinMargin());
    }

    public function testDerivesMaxRuntimeFromTimeoutForIteratedModule(): void
    {
        $monitorConfig = $this->create(
            $this->createStub(IteratedCronModuleInterface::class),
            '* * * * *',
            CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
            240,
            new SentryMonitorConfig(self::SLUG),
        );

        $this->assertSame(4, $monitorConfig->getMaxRuntime());
    }

    public function testDefaultsMaxRuntimeToThirtyMinutesForSimpleModule(): void
    {
        $monitorConfig = $this->create(
            $this->createStub(SimpleCronModuleInterface::class),
            '* * * * *',
            CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
            CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
            new SentryMonitorConfig(self::SLUG),
        );

        $this->assertSame(30, $monitorConfig->getMaxRuntime());
    }

    public function testUsesEffectiveCronExpressionForWildcardMinute(): void
    {
        $monitorConfig = $this->create(
            $this->createStub(SimpleCronModuleInterface::class),
            '* * * * *',
            5,
            CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
            new SentryMonitorConfig(self::SLUG),
        );

        $this->assertSame('*/5 * * * *', $monitorConfig->getSchedule()->getValue());
    }

    public function testFallsBackToDefaultTimezoneWhenCronTimeZoneNotSet(): void
    {
        $monitorConfig = $this->create(
            $this->createStub(SimpleCronModuleInterface::class),
            '* * * * *',
            CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
            CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
            new SentryMonitorConfig(self::SLUG),
            cronTimeZone: null,
        );

        $this->assertSame(date_default_timezone_get(), $monitorConfig->getTimezone());
    }

    private function create(
        SimpleCronModuleInterface|IteratedCronModuleInterface $service,
        string $cronExpression,
        int $runEveryMin,
        int $timeoutIteratedCronSec,
        SentryMonitorConfig $sentryMonitorConfig,
        ?string $cronTimeZone = 'Europe/Prague',
    ): MonitorConfig {
        $cronModuleConfig = new CronModuleConfig(
            $service,
            self::SERVICE_ID,
            $cronExpression,
            null,
            null,
            $runEveryMin,
            $timeoutIteratedCronSec,
            $sentryMonitorConfig,
        );

        return (new MonitorConfigFactory($cronTimeZone))->create($cronModuleConfig);
    }
}

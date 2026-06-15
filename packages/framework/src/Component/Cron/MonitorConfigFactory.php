<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use Sentry\MonitorConfig;
use Sentry\MonitorSchedule;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;

class MonitorConfigFactory
{
    protected const int DEFAULT_MAX_RUNTIME_MIN = 30;

    public function __construct(
        protected readonly ?string $cronTimeZone = null,
    ) {
    }

    public function create(CronModuleConfig $cronModuleConfig): MonitorConfig
    {
        $sentryMonitorConfig = $cronModuleConfig->getSentryMonitorConfig();

        $maxRuntime = $sentryMonitorConfig?->getMaxRuntime();

        if ($maxRuntime === null && $cronModuleConfig->getService() instanceof IteratedCronModuleInterface) {
            $maxRuntime = (int)ceil($cronModuleConfig->getTimeoutIteratedCronSec() / 60);
        }

        $maxRuntime = $maxRuntime ?? self::DEFAULT_MAX_RUNTIME_MIN;

        $checkinMargin = $sentryMonitorConfig?->getCheckinMargin() ?? $cronModuleConfig->getRunEveryMin();

        return new MonitorConfig(
            MonitorSchedule::crontab($cronModuleConfig->getEffectiveCronExpression()),
            checkinMargin: $checkinMargin > 0 ? $checkinMargin : null,
            maxRuntime: $maxRuntime > 0 ? $maxRuntime : null,
            timezone: $this->cronTimeZone ?? date_default_timezone_get(),
            failureIssueThreshold: $sentryMonitorConfig?->getFailureThreshold(),
            recoveryThreshold: $sentryMonitorConfig?->getRecoveryThreshold(),
        );
    }
}

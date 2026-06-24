<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use Psr\Log\LoggerInterface;
use Sentry\CheckInStatus;
use Sentry\MonitorConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Throwable;
use function Sentry\captureCheckIn;

class SentryCronMonitorFacade
{
    /**
     * @var array<string, string|null>
     */
    protected array $checkInIds = [];

    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly MonitorConfigFactory $monitorConfigFactory,
    ) {
    }

    public function reportStart(CronModuleConfig $cronModuleConfig): void
    {
        $this->runCheckIn(
            $cronModuleConfig,
            'start',
            function (string $slug, MonitorConfig $monitorConfig) use ($cronModuleConfig): void {
                $this->checkInIds[$cronModuleConfig->getServiceId()] = $this->captureCheckIn(
                    $slug,
                    CheckInStatus::inProgress(),
                    $monitorConfig,
                );
            },
        );
    }

    public function reportSuccess(CronModuleConfig $cronModuleConfig): void
    {
        $this->runCheckIn(
            $cronModuleConfig,
            'success',
            function (string $slug, MonitorConfig $monitorConfig) use ($cronModuleConfig): void {
                $this->captureCheckIn($slug, CheckInStatus::ok(), $monitorConfig, $this->pullCheckInId($cronModuleConfig));
            },
        );
    }

    public function reportFailure(CronModuleConfig $cronModuleConfig): void
    {
        $this->runCheckIn(
            $cronModuleConfig,
            'failure',
            function (string $slug, MonitorConfig $monitorConfig) use ($cronModuleConfig): void {
                $this->captureCheckIn($slug, CheckInStatus::error(), $monitorConfig, $this->pullCheckInId($cronModuleConfig));
            },
        );
    }

    /**
     * A disabled module sends no real check-ins, so Sentry would report its scheduled runs as missed.
     * Sending a paired start/success check-in keeps the monitor healthy while the module is intentionally disabled.
     */
    public function reportDisabledRunAsHealthy(CronModuleConfig $cronModuleConfig): void
    {
        $this->runCheckIn($cronModuleConfig, 'disabled-module success', function (string $slug, MonitorConfig $monitorConfig): void {
            $checkInId = $this->captureCheckIn($slug, CheckInStatus::inProgress(), $monitorConfig);
            $this->captureCheckIn($slug, CheckInStatus::ok(), $monitorConfig, $checkInId);
        });
    }

    /**
     * Runs the given check-in only when monitoring is enabled, swallowing and logging any Sentry transport error
     * so that a monitoring outage can never break cron execution.
     *
     * The monitor configuration is passed to the callback so that every check-in (including the closing ones)
     * carries it, letting Sentry upsert the monitor regardless of the order in which check-ins are ingested.
     *
     * @param callable(string, \Sentry\MonitorConfig): void $checkIn
     */
    protected function runCheckIn(CronModuleConfig $cronModuleConfig, string $checkInType, callable $checkIn): void
    {
        $sentryMonitorConfig = $cronModuleConfig->getSentryMonitorConfig();

        if ($sentryMonitorConfig === null) {
            return;
        }

        try {
            $checkIn($sentryMonitorConfig->getSlug(), $this->monitorConfigFactory->create($cronModuleConfig));
        } catch (Throwable $exception) {
            $this->logCheckInFailure($checkInType, $cronModuleConfig, $exception);
        }
    }

    protected function captureCheckIn(
        string $slug,
        CheckInStatus $status,
        ?MonitorConfig $monitorConfig = null,
        ?string $checkInId = null,
    ): ?string {
        return captureCheckIn(
            slug: $slug,
            status: $status,
            monitorConfig: $monitorConfig,
            checkInId: $checkInId,
        );
    }

    protected function pullCheckInId(CronModuleConfig $cronModuleConfig): ?string
    {
        $serviceId = $cronModuleConfig->getServiceId();
        $checkInId = $this->checkInIds[$serviceId] ?? null;
        unset($this->checkInIds[$serviceId]);

        return $checkInId;
    }

    protected function logCheckInFailure(
        string $checkInType,
        CronModuleConfig $cronModuleConfig,
        Throwable $exception,
    ): void {
        $this->logger->error(sprintf('Failed to send Sentry check-in (%s)', $checkInType), [
            'serviceId' => $cronModuleConfig->getServiceId(),
            'exception' => $exception,
        ]);
    }
}

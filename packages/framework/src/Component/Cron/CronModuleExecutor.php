<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use DateInterval;
use DateTimeImmutable;
use Monolog\Logger;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Bytes\BytesHelper;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class CronModuleExecutor
{
    public const RUN_STATUS_OK = 'ok';
    public const RUN_STATUS_TIMEOUT = 'timeout';
    public const RUN_STATUS_SUSPENDED = 'suspended';

    protected DateTimeImmutable $startedAt;

    public function __construct(
        protected readonly CronConfig $cronConfig,
        protected readonly Logger $logger,
        protected readonly BytesHelper $bytesHelper,
        protected readonly ClockInterface $clock,
    ) {
        $this->startedAt = $this->clock->now();
    }

    /**
     * @param \Shopsys\Plugin\Cron\SimpleCronModuleInterface|\Shopsys\Plugin\Cron\IteratedCronModuleInterface $cronModuleService
     * @param bool $suspended
     * @return string
     */
    public function runModule($cronModuleService, $suspended)
    {
        $cronConfig = $this->cronConfig->getCronModuleConfigByServiceId(get_class($cronModuleService));

        if (!$this->canRun($cronConfig)) {
            return self::RUN_STATUS_TIMEOUT;
        }

        if ($cronModuleService instanceof SimpleCronModuleInterface) {
            $cronModuleService->run();

            return self::RUN_STATUS_OK;
        }

        if ($cronModuleService instanceof IteratedCronModuleInterface) {
            if ($suspended) {
                $cronModuleService->wakeUp();
            }
            $inProgress = true;

            while ($inProgress === true && $this->canRun($cronConfig)) {
                $inProgress = $cronModuleService->iterate();
            }

            if ($inProgress === true) {
                $cronModuleService->sleep();

                return self::RUN_STATUS_SUSPENDED;
            }

            return self::RUN_STATUS_OK;
        }

        return self::RUN_STATUS_OK;
    }

    /**
     * @phpstan-impure
     */
    public function canRun(CronModuleConfig $cronConfig): bool
    {
        $canRunUntil = $this->startedAt->add(
            DateInterval::createFromDateString($cronConfig->getTimeoutIteratedCronSec() . ' seconds'),
        );

        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->bytesHelper->getPhpMemoryLimitInBytes();

        if ($memoryLimit !== -1 && $memoryUsage >= $memoryLimit * 0.9) {
            $this->logger->error('Cron was running out of memory, so it was put to sleep to prevent failure.', [
                'service_id' => $cronConfig->getServiceId(),
                'instance_name' => $cronConfig->getInstanceName(),
                'memory_usage' => BytesHelper::convertBytesToReadableString($memoryUsage),
                'memory_limit' => BytesHelper::convertBytesToReadableString($memoryLimit),
            ]);

            return false;
        }

        return $canRunUntil > $this->clock->now();
    }
}

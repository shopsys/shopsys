<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use DateInterval;
use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\Bytes\BytesHelper;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class CronModuleExecutor
{
    public const RUN_STATUS_OK = 'ok';
    public const RUN_STATUS_TIMEOUT = 'timeout';
    public const RUN_STATUS_SUSPENDED = 'suspended';

    protected DateTimeImmutable $startedAt;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig $cronConfig
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function __construct(
        protected readonly CronConfig $cronConfig,
        protected readonly Logger $logger,
    ) {
        $this->startedAt = new DateTimeImmutable('now');
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
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig $cronConfig
     * @return bool
     */
    public function canRun(CronModuleConfig $cronConfig): bool
    {
        $canRunUntil = $this->startedAt->add(
            DateInterval::createFromDateString($cronConfig->getTimeoutIteratedCronSec() . ' seconds'),
        );

        $memoryUsage = memory_get_usage(true);
        $memoryLimit = BytesHelper::getPhpMemoryLimitInBytes();

        if ($memoryLimit !== -1 && $memoryUsage >= $memoryLimit * 0.9) {
            $this->logger->info('Cron was running out of memory, so it was put to sleep to prevent failure.');

            return false;
        }

        return $canRunUntil > new DateTimeImmutable('now');
    }
}

<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

use DateInterval;
use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\DependencyInjection\SetterInjectionTrait;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class CronModuleExecutor
{
    use SetterInjectionTrait;

    public const RUN_STATUS_OK = 'ok';
    public const RUN_STATUS_TIMEOUT = 'timeout';
    public const RUN_STATUS_SUSPENDED = 'suspended';

    /**
     * @deprecated This will be removed in next major version
     * @var \DateTimeImmutable|null
     */
    protected $canRunTo;

    /**
     * @var \DateTimeImmutable
     */
    protected DateTimeImmutable $startedAt;

    /**
     * @param int $secondsTimeout
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig|null $cronConfig
     */
    public function __construct(
        int $secondsTimeout,
        protected ?CronConfig $cronConfig = null,
    ) {
        $this->canRunTo = new DateTimeImmutable('+' . $secondsTimeout . ' sec');
        $this->startedAt = new DateTimeImmutable('now');
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig $cronConfig
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setCronConfig(CronConfig $cronConfig): void
    {
        $this->setDependency($cronConfig, 'cronConfig');
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
            while ($this->canRun($cronConfig) && $inProgress === true) {
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
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig|null $cronConfig
     * @return bool
     */
    public function canRun(?CronModuleConfig $cronConfig = null): bool
    {
        if ($cronConfig !== null) {
            $canRunUntil = $this->startedAt->add(
                DateInterval::createFromDateString($cronConfig->getTimeoutIteratedCronSec() . ' seconds')
            );

            return $canRunUntil > new DateTimeImmutable('now');
        }

        return $this->canRunTo > new DateTimeImmutable();
    }
}

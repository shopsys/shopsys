<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

use DateTimeImmutable;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class CronModuleExecutor
{
    const RUN_STATUS_OK = 'ok';
    const RUN_STATUS_TIMEOUT = 'timeout';
    const RUN_STATUS_SUSPENDED = 'suspended';

    /**
     * @var \DateTimeImmutable|null
     */
    private $canRunTo;

    public function __construct(int $secondsTimeout)
    {
        $this->canRunTo = new DateTimeImmutable('+' . $secondsTimeout . ' sec');
    }

    /**
     * @param \Shopsys\Plugin\Cron\SimpleCronModuleInterface|\Shopsys\Plugin\Cron\IteratedCronModuleInterface $cronModuleService
     * @param bool $suspended
     */
    public function runModule($cronModuleService, $suspended): string
    {
        if (!$this->canRun()) {
            return self::RUN_STATUS_TIMEOUT;
        }

        if ($cronModuleService instanceof SimpleCronModuleInterface) {
            $cronModuleService->run();

            return self::RUN_STATUS_OK;
        } elseif ($cronModuleService instanceof IteratedCronModuleInterface) {
            if ($suspended) {
                $cronModuleService->wakeUp();
            }
            $inProgress = true;
            while ($this->canRun() && $inProgress === true) {
                $inProgress = $cronModuleService->iterate();
            }
            if ($inProgress === true) {
                $cronModuleService->sleep();
                return self::RUN_STATUS_SUSPENDED;
            } else {
                return self::RUN_STATUS_OK;
            }
        }
    }

    public function canRun(): bool
    {
        return $this->canRunTo > new DateTimeImmutable();
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron\Config;

use Shopsys\FrameworkBundle\Component\Cron\CronTimeInterface;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class CronModuleConfig implements CronTimeInterface
{
    public const DEFAULT_INSTANCE_NAME = 'default';
    public const RUN_EVERY_MIN_DEFAULT = 5;
    public const TIMEOUT_ITERATED_CRON_SEC_DEFAULT = 240;

    protected string $instanceName;

    /**
     * @param \Shopsys\Plugin\Cron\SimpleCronModuleInterface|\Shopsys\Plugin\Cron\IteratedCronModuleInterface $service
     * @param string $serviceId
     * @param string $timeHours
     * @param string $timeMinutes
     * @param string|null $readableName
     * @param int $runEveryMin
     * @param int $timeoutIteratedCronSec
     */
    public function __construct(
        protected readonly SimpleCronModuleInterface|IteratedCronModuleInterface $service,
        protected readonly string $serviceId,
        protected readonly string $timeHours,
        protected readonly string $timeMinutes,
        protected readonly ?string $readableName = null,
        protected readonly int $runEveryMin = self::RUN_EVERY_MIN_DEFAULT,
        protected readonly int $timeoutIteratedCronSec = self::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
    ) {
        $this->assignToInstance(self::DEFAULT_INSTANCE_NAME);
    }

    /**
     * @return \Shopsys\Plugin\Cron\SimpleCronModuleInterface|\Shopsys\Plugin\Cron\IteratedCronModuleInterface
     */
    public function getService(): SimpleCronModuleInterface|IteratedCronModuleInterface
    {
        return $this->service;
    }

    /**
     * @return string
     */
    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    /**
     * @return string
     */
    public function getTimeMinutes(): string
    {
        return $this->timeMinutes;
    }

    /**
     * @return string
     */
    public function getTimeHours(): string
    {
        return $this->timeHours;
    }

    /**
     * @return string|null
     */
    public function getReadableName(): ?string
    {
        return $this->readableName;
    }

    /**
     * @return string
     */
    public function getInstanceName(): string
    {
        return $this->instanceName;
    }

    /**
     * @param string $instanceName
     */
    public function assignToInstance(string $instanceName): void
    {
        $this->instanceName = $instanceName;
    }

    /**
     * @return string
     */
    public function getReadableFrequency(): string
    {
        if ($this->timeHours === '*' && $this->timeMinutes === '*') {
            return t('On each run (usually every 5 minutes)');
        }

        if ($this->timeHours === '*' && is_numeric($this->timeMinutes)) {
            return t('Every hour');
        }

        if (is_numeric($this->timeHours) && $this->timeMinutes === '*') {
            return t('Every 5 minutes in %hour% hour', [
                '%hour%' => (int)$this->timeHours,
                '%count%' => (int)$this->timeHours,
            ]);
        }

        if (is_numeric($this->timeHours) && is_numeric($this->timeMinutes)) {
            return t('Everyday at %hour%:%minutes%', [
                '%hour%' => date('H', (int)$this->timeHours * 3600),
                '%minutes%' => date('i', (int)$this->timeMinutes * 60),
            ]);
        }

        return t('Several times a day (%hours%h and %minutes%m)', [
            '%hours%' => $this->timeHours,
            '%minutes%' => $this->timeMinutes,
        ]);
    }

    /**
     * @return int
     */
    public function getRunEveryMin(): int
    {
        return $this->runEveryMin;
    }

    /**
     * @return int
     */
    public function getTimeoutIteratedCronSec(): int
    {
        return $this->timeoutIteratedCronSec;
    }
}

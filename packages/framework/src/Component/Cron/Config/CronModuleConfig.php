<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron\Config;

use Shopsys\FrameworkBundle\Component\Cron\CronTimeInterface;

class CronModuleConfig implements CronTimeInterface
{
    public const DEFAULT_INSTANCE_NAME = 'default';

    /**
     * @var \Shopsys\Plugin\Cron\SimpleCronModuleInterface
     */
    protected $service;

    /**
     * @var string
     */
    protected $serviceId;

    /**
     * @var string
     */
    protected $timeMinutes;

    /**
     * @var string
     */
    protected $timeHours;

    /**
     * @var string
     */
    protected $instanceName;

    /**
     * @var string
     */
    protected $readableName;

    /**
     * @param \Shopsys\Plugin\Cron\SimpleCronModuleInterface|\Shopsys\Plugin\Cron\IteratedCronModuleInterface $service
     * @param string $serviceId
     * @param string $timeHours
     * @param string $timeMinutes
     * @param string|null $readableName
     */
    public function __construct($service, $serviceId, $timeHours, $timeMinutes, ?string $readableName = null)
    {
        $this->service = $service;
        $this->serviceId = $serviceId;
        $this->timeHours = $timeHours;
        $this->timeMinutes = $timeMinutes;
        $this->readableName = $readableName;
        $this->assignToInstance(self::DEFAULT_INSTANCE_NAME);
    }

    /**
     * @return \Shopsys\Plugin\Cron\SimpleCronModuleInterface|\Shopsys\Plugin\Cron\IteratedCronModuleInterface
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @return string
     */
    public function getTimeMinutes()
    {
        return $this->timeMinutes;
    }

    /**
     * @return string
     */
    public function getTimeHours()
    {
        return $this->timeHours;
    }

    /**
     * @return string
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
            return t('Every 5 minutes');
        }

        if ($this->timeHours === '*' && is_numeric($this->timeMinutes)) {
            if (date('i', (int)$this->timeMinutes) === '00') {
                return t('Every hour');
            }

            return t('Every hour and %minutes% minutes', ['%minutes%' => date('i', (int)$this->timeMinutes)]);
        }

        if (is_numeric($this->timeHours) && $this->timeMinutes === '*') {
            return t('Every 5 minutes in %hour% hour', ['%hour%' => date('H', (int)$this->timeHours)]);
        }

        if (is_numeric($this->timeHours) && is_numeric($this->timeMinutes)) {
            return t('Everyday at %hour%:%minutes%', [
                '%hour%' => date('H', (int)$this->timeHours),
                '%minutes%' => date('i', (int)$this->timeMinutes),
            ]);
        }

        return t('Several times a day (%hours%h and %minutes%m)', [
            '%hours%' => $this->timeHours,
            '%minutes%' => $this->timeMinutes,
        ]);
    }
}

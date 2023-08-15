<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron\Config;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Component\Cron\Config\Exception\CronModuleConfigNotFoundException;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver;
use Shopsys\FrameworkBundle\Component\Cron\Exception\InvalidCronModuleException;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class CronConfig
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    protected array $cronModuleConfigs;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver $cronTimeResolver
     */
    public function __construct(protected readonly CronTimeResolver $cronTimeResolver)
    {
        $this->cronModuleConfigs = [];
    }

    /**
     * @param \Shopsys\Plugin\Cron\SimpleCronModuleInterface|\Shopsys\Plugin\Cron\IteratedCronModuleInterface|mixed $service
     * @param string $serviceId
     * @param string $timeHours
     * @param string $timeMinutes
     * @param string $instanceName
     * @param string|null $readableName
     * @param int $runEveryMin
     * @param int $timeoutIteratedCronSec
     */
    public function registerCronModuleInstance(
        $service,
        string $serviceId,
        string $timeHours,
        string $timeMinutes,
        string $instanceName,
        ?string $readableName = null,
        int $runEveryMin = CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
        int $timeoutIteratedCronSec = CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
    ): void {
        if (!$service instanceof SimpleCronModuleInterface && !$service instanceof IteratedCronModuleInterface) {
            throw new InvalidCronModuleException($serviceId);
        }
        $this->cronTimeResolver->validateTimeString($timeHours, 23, 1);
        $this->cronTimeResolver->validateTimeString($timeMinutes, 55, 5);

        $cronModuleConfig = new CronModuleConfig($service, $serviceId, $timeHours, $timeMinutes, $readableName, $runEveryMin, $timeoutIteratedCronSec);
        $cronModuleConfig->assignToInstance($instanceName);

        $this->cronModuleConfigs[] = $cronModuleConfig;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    public function getAllCronModuleConfigs()
    {
        return $this->cronModuleConfigs;
    }

    /**
     * @param \DateTimeInterface $roundedTime
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    public function getCronModuleConfigsByTime(DateTimeInterface $roundedTime)
    {
        $matchedCronConfigs = [];

        foreach ($this->cronModuleConfigs as $cronConfig) {
            if ($this->cronTimeResolver->isValidAtTime($cronConfig, $roundedTime)) {
                $matchedCronConfigs[] = $cronConfig;
            }
        }

        return $matchedCronConfigs;
    }

    /**
     * @param string $serviceId
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig
     */
    public function getCronModuleConfigByServiceId($serviceId)
    {
        foreach ($this->cronModuleConfigs as $cronConfig) {
            if ($cronConfig->getServiceId() === $serviceId) {
                return $cronConfig;
            }
        }

        throw new CronModuleConfigNotFoundException($serviceId);
    }

    /**
     * @param string $instanceName
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    public function getCronModuleConfigsForInstance(string $instanceName): array
    {
        $matchedCronConfigs = [];

        foreach ($this->cronModuleConfigs as $cronModuleConfig) {
            if ($cronModuleConfig->getInstanceName() === $instanceName) {
                $matchedCronConfigs[] = $cronModuleConfig;
            }
        }

        return $matchedCronConfigs;
    }
}

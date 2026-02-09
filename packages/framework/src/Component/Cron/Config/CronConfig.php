<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron\Config;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Component\Cron\Config\Exception\CronModuleConfigNotFoundException;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class CronConfig
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    protected array $cronModuleConfigs;

    public function __construct(
        protected readonly CronTimeResolver $cronTimeResolver,
    ) {
        $this->cronModuleConfigs = [];
    }

    public function registerCronModuleInstance(
        SimpleCronModuleInterface|IteratedCronModuleInterface $service,
        string $serviceId,
        string $timeHours,
        string $timeMinutes,
        string $instanceName,
        ?string $readableName = null,
        ?string $readableFrequency = null,
        int $runEveryMin = CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
        int $timeoutIteratedCronSec = CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
    ): void {
        $this->cronTimeResolver->validateTimeString($timeHours, 23, 1);
        $this->cronTimeResolver->validateTimeString($timeMinutes, 55, 1);

        $cronModuleConfig = new CronModuleConfig($service, $serviceId, $timeHours, $timeMinutes, $readableName, $readableFrequency, $runEveryMin, $timeoutIteratedCronSec);
        $cronModuleConfig->assignToInstance($instanceName);

        $this->cronModuleConfigs[] = $cronModuleConfig;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    public function getAllCronModuleConfigs(): array
    {
        return $this->cronModuleConfigs;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    public function getCronModuleConfigsByTime(DateTimeInterface $roundedTime): array
    {
        $matchedCronConfigs = [];

        foreach ($this->cronModuleConfigs as $cronConfig) {
            if ($this->cronTimeResolver->isValidAtTime($cronConfig, $roundedTime)) {
                $matchedCronConfigs[] = $cronConfig;
            }
        }

        return $matchedCronConfigs;
    }

    public function getCronModuleConfigByServiceId(string $serviceId): CronModuleConfig
    {
        foreach ($this->cronModuleConfigs as $cronConfig) {
            if ($cronConfig->getServiceId() === $serviceId) {
                return $cronConfig;
            }
        }

        throw new CronModuleConfigNotFoundException($serviceId);
    }

    /**
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

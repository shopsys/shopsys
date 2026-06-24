<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron\Config;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Component\Cron\Config\Exception\CronModuleConfigNotFoundException;
use Shopsys\FrameworkBundle\Component\Cron\Config\Exception\SentryMonitoringNotEnabledException;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class CronConfig
{
    protected const int MAX_SENTRY_SLUG_LENGTH = 50;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    protected array $cronModuleConfigs;

    public function __construct(
        protected readonly CronTimeResolver $cronTimeResolver,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
        $this->cronModuleConfigs = [];
    }

    public function registerCronModuleInstance(
        SimpleCronModuleInterface|IteratedCronModuleInterface $service,
        string $serviceId,
        string $cronExpression,
        string $instanceName,
        ?string $readableName = null,
        ?string $readableFrequency = null,
        int $runEveryMin = CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
        int $timeoutIteratedCronSec = CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
        bool $sentryMonitoring = false,
        ?int $sentryMaxRuntime = null,
        ?int $sentryCheckinMargin = null,
        ?int $sentryFailureThreshold = null,
        ?int $sentryRecoveryThreshold = null,
    ): void {
        $this->cronTimeResolver->validateCronExpression($cronExpression);

        $cronModuleConfig = new CronModuleConfig(
            $service,
            $serviceId,
            $cronExpression,
            $readableName,
            $readableFrequency,
            $runEveryMin,
            $timeoutIteratedCronSec,
            $this->createSentryMonitorConfig(
                $serviceId,
                $sentryMonitoring,
                $sentryMaxRuntime,
                $sentryCheckinMargin,
                $sentryFailureThreshold,
                $sentryRecoveryThreshold,
            ),
        );
        $cronModuleConfig->assignToInstance($instanceName);

        $this->cronModuleConfigs[] = $cronModuleConfig;
    }

    protected function createSentryMonitorConfig(
        string $serviceId,
        bool $sentryMonitoring,
        ?int $sentryMaxRuntime,
        ?int $sentryCheckinMargin,
        ?int $sentryFailureThreshold,
        ?int $sentryRecoveryThreshold,
    ): ?SentryMonitorConfig {
        if (!$sentryMonitoring) {
            $hasSentryOptions = $sentryMaxRuntime !== null
                || $sentryCheckinMargin !== null
                || $sentryFailureThreshold !== null
                || $sentryRecoveryThreshold !== null;

            if ($hasSentryOptions) {
                throw new SentryMonitoringNotEnabledException($serviceId);
            }

            return null;
        }

        return new SentryMonitorConfig(
            $this->buildSentryMonitorSlug($serviceId),
            $sentryMaxRuntime,
            $sentryCheckinMargin,
            $sentryFailureThreshold,
            $sentryRecoveryThreshold,
        );
    }

    protected function buildSentryMonitorSlug(string $serviceId): string
    {
        $className = basename(str_replace('\\', '/', $serviceId));
        $className = $this->transformStringHelper->removeStringFromEnd($className, 'CronModule');
        $classSlug = $this->transformStringHelper->stringToFriendlyUrlSlug($className);

        $suffix = '-' . substr(md5($serviceId), 0, 6);

        return rtrim(substr($classSlug, 0, self::MAX_SENTRY_SLUG_LENGTH - strlen($suffix)), '-') . $suffix;
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

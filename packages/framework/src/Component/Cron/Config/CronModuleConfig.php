<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron\Config;

use Lorisleiva\CronTranslator\CronParsingException;
use Lorisleiva\CronTranslator\CronTranslator;
use Override;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeInterface;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class CronModuleConfig implements CronTimeInterface
{
    public const string DEFAULT_INSTANCE_NAME = 'default';
    public const int RUN_EVERY_MIN_DEFAULT = 5;
    public const int TIMEOUT_ITERATED_CRON_SEC_DEFAULT = 240;

    protected string $instanceName;

    public function __construct(
        protected readonly SimpleCronModuleInterface|IteratedCronModuleInterface $service,
        protected readonly string $serviceId,
        protected readonly string $cronExpression,
        protected readonly ?string $readableName = null,
        protected readonly ?string $readableFrequency = null,
        protected readonly int $runEveryMin = self::RUN_EVERY_MIN_DEFAULT,
        protected readonly int $timeoutIteratedCronSec = self::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
        protected readonly ?SentryMonitorConfig $sentryMonitorConfig = null,
    ) {
        $this->assignToInstance(self::DEFAULT_INSTANCE_NAME);
    }

    public function getService(): SimpleCronModuleInterface|IteratedCronModuleInterface
    {
        return $this->service;
    }

    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    #[Override]
    public function getCronExpression(): string
    {
        return $this->cronExpression;
    }

    public function getReadableName(): ?string
    {
        return $this->readableName;
    }

    public function getInstanceName(): string
    {
        return $this->instanceName;
    }

    public function assignToInstance(string $instanceName): void
    {
        $this->instanceName = $instanceName;
    }

    public function getReadableFrequency(string $locale = 'en'): string
    {
        if ($this->readableFrequency !== null) {
            return $this->readableFrequency;
        }

        $expression = $this->getEffectiveCronExpression();

        try {
            return CronTranslator::translate($expression, $locale, true);
        } catch (CronParsingException) {
            return CronTranslator::translate($expression, 'en', true);
        }
    }

    /**
     * Replaces wildcard minute field with the actual run interval so that
     * the translated frequency reflects reality (e.g. "every 5 minutes" instead of "every minute").
     */
    public function getEffectiveCronExpression(): string
    {
        $parts = explode(' ', $this->cronExpression);

        if ($parts[0] === '*' && $this->runEveryMin > 1) {
            $parts[0] = '*/' . $this->runEveryMin;
        }

        return implode(' ', $parts);
    }

    public function getRunEveryMin(): int
    {
        return $this->runEveryMin;
    }

    public function getTimeoutIteratedCronSec(): int
    {
        return $this->timeoutIteratedCronSec;
    }

    public function getSentryMonitorConfig(): ?SentryMonitorConfig
    {
        return $this->sentryMonitorConfig;
    }
}

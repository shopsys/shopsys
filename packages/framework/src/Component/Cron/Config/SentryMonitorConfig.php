<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron\Config;

class SentryMonitorConfig
{
    public function __construct(
        protected readonly string $slug,
        protected readonly ?int $maxRuntime = null,
        protected readonly ?int $checkinMargin = null,
        protected readonly ?int $failureThreshold = null,
        protected readonly ?int $recoveryThreshold = null,
    ) {
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getMaxRuntime(): ?int
    {
        return $this->maxRuntime;
    }

    public function getCheckinMargin(): ?int
    {
        return $this->checkinMargin;
    }

    public function getFailureThreshold(): ?int
    {
        return $this->failureThreshold;
    }

    public function getRecoveryThreshold(): ?int
    {
        return $this->recoveryThreshold;
    }
}

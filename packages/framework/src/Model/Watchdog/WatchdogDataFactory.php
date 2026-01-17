<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use Psr\Clock\ClockInterface;

class WatchdogDataFactory
{
    public function __construct(
        protected readonly ClockInterface $clock,
    ) {
    }

    protected function createInstance(): WatchdogData
    {
        return new WatchdogData();
    }

    public function createByDomainId(int $domainId): WatchdogData
    {
        $watchdogData = $this->createInstance();
        $watchdogData->domainId = $domainId;

        return $watchdogData;
    }

    public function createFromWatchdog(Watchdog $watchdog): WatchdogData
    {
        $watchdogData = $this->createInstance();
        $this->fillFromWatchdog($watchdogData, $watchdog);

        return $watchdogData;
    }

    protected function fillFromWatchdog(WatchdogData $watchdogData, Watchdog $watchdog): void
    {
        $watchdogData->email = $watchdog->getEmail();
        $watchdogData->product = $watchdog->getProduct();
    }
}

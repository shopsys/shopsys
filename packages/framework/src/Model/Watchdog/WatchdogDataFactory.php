<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use Psr\Clock\ClockInterface;

class WatchdogDataFactory
{
    /**
     * @param \Psr\Clock\ClockInterface $clock
     */
    public function __construct(
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogData
     */
    protected function createInstance(): WatchdogData
    {
        return new WatchdogData();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogData
     */
    public function createByDomainId(int $domainId): WatchdogData
    {
        $watchdogData = $this->createInstance();
        $watchdogData->domainId = $domainId;

        return $watchdogData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog $watchdog
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogData
     */
    public function createFromWatchdog(Watchdog $watchdog): WatchdogData
    {
        $watchdogData = $this->createInstance();
        $this->fillFromWatchdog($watchdogData, $watchdog);

        return $watchdogData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogData $watchdogData
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog $watchdog
     */
    protected function fillFromWatchdog(WatchdogData $watchdogData, Watchdog $watchdog): void
    {
        $watchdogData->email = $watchdog->getEmail();
        $watchdogData->product = $watchdog->getProduct();
    }
}

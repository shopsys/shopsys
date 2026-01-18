<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use Monolog\Logger;
use Override;
use Shopsys\FrameworkBundle\Model\Watchdog\Mail\WatchdogMailFacade;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;

class WatchdogCronModule implements IteratedCronModuleInterface
{
    protected Logger $logger;

    public function __construct(
        protected readonly WatchdogFacade $watchdogFacade,
        protected readonly WatchdogMailFacade $watchdogMailFacade,
    ) {
    }

    #[Override]
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    #[Override]
    public function wakeUp(): void
    {
    }

    #[Override]
    public function iterate(): bool
    {
        $watchdog = $this->watchdogFacade->findNextWatchdogToSend();

        if ($watchdog === null) {
            return false;
        }

        $this->watchdogMailFacade->sendMail($watchdog);

        $this->logger->info('Sending watchdog email.', [
            'watchdogId' => $watchdog->getId(),
            'watchdogProductId' => $watchdog->getProduct()->getId(),
        ]);

        $this->watchdogFacade->deleteById($watchdog->getId());

        return true;
    }

    #[Override]
    public function sleep(): void
    {
    }
}

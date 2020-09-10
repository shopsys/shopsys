<?php

declare(strict_types=1);


namespace App\Component\Cron;

use App\Command\CronLockCommand;
use NinjaMutex\Lock\LockInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CronLockEventListener implements EventSubscriberInterface
{
    /**
     * @var \NinjaMutex\Lock\LockInterface
     */
    private $lock;

    public function __construct(LockInterface $lock)
    {
        $this->lock = $lock;
    }

    /**
     * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        if ($event->getCommand()->getName() === 'shopsys:cron') {
            if ($this->lock->isLocked(CronLockCommand::CRON_MUTEX_LOCK_NAME) === true) {
                $event->getOutput()->writeln('Cron command is disabled due to deployment. If deployment is not in progress, you need to delete this pod to be able to run cron.');
                $event->disableCommand();
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => ['onConsoleCommand'],
        ];
    }
}

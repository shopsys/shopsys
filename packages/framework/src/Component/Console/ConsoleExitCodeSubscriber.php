<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Console;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Taken from https://github.com/VasekPurchart/Console-Errors-Bundle/blob/master/src/Console/ConsoleExitCodeListener.php
 * because so far, the bundle does not support PHP 8, @see https://github.com/VasekPurchart/Console-Errors-Bundle/issues/11
 */
class ConsoleExitCodeSubscriber implements EventSubscriberInterface
{
    protected const LOG_LEVEL = LogLevel::ERROR;

    protected LoggerInterface $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param \Symfony\Component\Console\Event\ConsoleTerminateEvent $event
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        $statusCode = $event->getExitCode();
        $command = $event->getCommand();

        if ($statusCode === 0) {
            return;
        }

        if ($statusCode > 255) {
            $statusCode = 255;
            $event->setExitCode($statusCode);
        }

        $this->logger->log(static::LOG_LEVEL, sprintf(
            'Command `%s` exited with status code %d',
            $command->getName(),
            $statusCode
        ));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::TERMINATE => 'onConsoleTerminate',
        ];
    }
}

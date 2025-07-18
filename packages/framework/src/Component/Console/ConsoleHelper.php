<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Console;

use Override;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConsoleHelper implements EventSubscriberInterface
{
    private ?ConsoleCommandEvent $consoleCommandEvent = null;

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleCommandEvent::class => ['onConsoleCommand', 999],
        ];
    }

    /**
     * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $this->consoleCommandEvent = $event;
    }

    /**
     * @param string $pattern
     * @return bool
     */
    public function isCommandMatching(string $pattern): bool
    {
        $commandName = $this->getRunningCommand()?->getName();

        if ($commandName === null) {
            return false;
        }

        return str_starts_with($commandName, $pattern);
    }

    /**
     * @return bool
     */
    public function isConsole(): bool
    {
        return $this->consoleCommandEvent !== null;
    }

    /**
     * @return \Symfony\Component\Console\Command\Command|null
     */
    public function getRunningCommand(): ?Command
    {
        return $this->consoleCommandEvent?->getCommand();
    }
}

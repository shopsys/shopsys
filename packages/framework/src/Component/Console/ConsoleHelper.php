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

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleCommandEvent::class => ['onConsoleCommand', 999],
        ];
    }

    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $this->consoleCommandEvent = $event;
    }

    public function isCommandMatching(string $pattern): bool
    {
        $commandName = $this->getRunningCommand()?->getName();

        if ($commandName === null) {
            return false;
        }

        return str_starts_with($commandName, $pattern);
    }

    public function isConsole(): bool
    {
        return $this->consoleCommandEvent !== null;
    }

    public function getRunningCommand(): ?Command
    {
        return $this->consoleCommandEvent?->getCommand();
    }
}

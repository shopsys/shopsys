<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched by shopsys:migrations:migrate inside the migration transaction, after the migrations and the schema check,
 * even when there was no migration to execute. Listeners can (re)create database objects derived from the final schema
 * and add messages that the command prints.
 */
class MigrationsFinishedEvent extends Event
{
    /**
     * @var array<string>
     */
    protected array $messages = [];

    public function addMessage(string $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * @return array<string>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}

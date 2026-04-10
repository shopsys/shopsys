<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class DatabaseSchemaMigratedEvent extends Event
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

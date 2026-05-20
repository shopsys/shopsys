<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class DatabaseSchemaPreparedEvent extends Event
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

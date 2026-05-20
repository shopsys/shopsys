<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Availability;

class McpAvailabilityChecker
{
    public function __construct(
        protected readonly string $mcpDatabaseUser,
        protected readonly string $mcpDatabasePassword,
    ) {
    }

    public function isAvailable(): bool
    {
        return trim($this->mcpDatabaseUser) !== '' && trim($this->mcpDatabasePassword) !== '';
    }
}

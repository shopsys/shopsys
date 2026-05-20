<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Middleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Override;
use Shopsys\McpBundle\Component\Database\Middleware\Driver\McpSessionSettingsDriver;

class McpSessionSettingsMiddleware implements Middleware
{
    public function __construct(
        protected readonly int $statementTimeoutMilliseconds,
        protected readonly int $lockTimeoutMilliseconds,
    ) {
    }

    #[Override]
    public function wrap(Driver $driver): Driver
    {
        return new McpSessionSettingsDriver(
            $driver,
            $this->statementTimeoutMilliseconds,
            $this->lockTimeoutMilliseconds,
        );
    }
}

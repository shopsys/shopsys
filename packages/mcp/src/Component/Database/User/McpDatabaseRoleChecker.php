<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\User;

use Doctrine\DBAL\Connection;

class McpDatabaseRoleChecker
{
    public function __construct(
        protected readonly Connection $defaultConnection,
        protected readonly string $mcpDatabaseUser,
    ) {
    }

    public function exists(): bool
    {
        return (bool)$this->defaultConnection->fetchOne(
            'SELECT 1 FROM pg_roles WHERE rolname = ?',
            [$this->mcpDatabaseUser],
        );
    }

    public function canUsePublicSchema(): bool
    {
        return (bool)$this->defaultConnection->fetchOne(
            'SELECT has_schema_privilege(?, \'public\', \'USAGE\')',
            [$this->mcpDatabaseUser],
        );
    }
}

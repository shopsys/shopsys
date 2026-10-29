<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\User;

use Doctrine\DBAL\Connection;

class McpDatabaseRoleRepository
{
    public function __construct(
        protected readonly Connection $defaultConnection,
    ) {
    }

    public function roleExists(string $roleName): bool
    {
        return (bool)$this->defaultConnection->fetchOne(
            'SELECT 1 FROM pg_roles WHERE rolname = ?',
            [$roleName],
        );
    }

    public function canRoleUsePublicSchema(string $roleName): bool
    {
        return (bool)$this->defaultConnection->fetchOne(
            'SELECT has_schema_privilege(?, \'public\', \'USAGE\')',
            [$roleName],
        );
    }
}

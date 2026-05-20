<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\User;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use RuntimeException;

class McpReadOnlyUserManager
{
    public const int CONNECTION_LIMIT = 10;

    public function __construct(
        protected readonly Connection $defaultConnection,
        protected readonly string $databaseUser,
        protected readonly string $mcpDatabaseUser,
        protected readonly string $mcpDatabasePassword,
    ) {
    }

    public function ensureReadOnlyUser(): string
    {
        $this->validateConfiguration();

        try {
            $this->defaultConnection->transactional(function (): void {
                $this->ensureRole();
                $this->ensureDatabasePrivileges();
                $this->ensureSchemaPrivileges();
                $this->ensureDefaultPrivileges();
            });
        } catch (Exception $exception) {
            throw new RuntimeException(
                sprintf(
                    'MCP read-only database role "%s" could not be prepared. Ensure the active database connection can manage roles and grants.',
                    $this->mcpDatabaseUser,
                ),
                previous: $exception,
            );
        }

        return $this->mcpDatabaseUser;
    }

    protected function validateConfiguration(): void
    {
        if (trim($this->mcpDatabaseUser) === '') {
            throw new RuntimeException('MCP_DATABASE_USER must not be empty.');
        }

        if ($this->databaseUser === $this->mcpDatabaseUser) {
            throw new RuntimeException('MCP_DATABASE_USER must differ from the application database owner so MCP can use a dedicated read-only role.');
        }

        if (trim($this->mcpDatabasePassword) === '') {
            throw new RuntimeException('MCP_DATABASE_PASSWORD must not be empty.');
        }
    }

    protected function ensureRole(): void
    {
        $roleStatementAction = $this->mcpUserExists() ? 'ALTER' : 'CREATE';

        $this->defaultConnection->executeStatement(sprintf(
            '%s ROLE %s WITH LOGIN PASSWORD %s CONNECTION LIMIT %d NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION NOBYPASSRLS',
            $roleStatementAction,
            $this->defaultConnection->quoteSingleIdentifier($this->mcpDatabaseUser),
            $this->defaultConnection->quote($this->mcpDatabasePassword),
            self::CONNECTION_LIMIT,
        ));
    }

    protected function ensureDatabasePrivileges(): void
    {
        $databaseName = (string)$this->defaultConnection->fetchOne('SELECT current_database()');

        $this->defaultConnection->executeStatement(sprintf(
            'REVOKE ALL PRIVILEGES ON DATABASE %s FROM %s',
            $this->defaultConnection->quoteSingleIdentifier($databaseName),
            $this->defaultConnection->quoteSingleIdentifier($this->mcpDatabaseUser),
        ));
        $this->defaultConnection->executeStatement(sprintf(
            'GRANT CONNECT ON DATABASE %s TO %s',
            $this->defaultConnection->quoteSingleIdentifier($databaseName),
            $this->defaultConnection->quoteSingleIdentifier($this->mcpDatabaseUser),
        ));
    }

    protected function ensureSchemaPrivileges(): void
    {
        $this->defaultConnection->executeStatement(sprintf(
            'REVOKE CREATE ON SCHEMA public FROM %s',
            $this->defaultConnection->quoteSingleIdentifier($this->mcpDatabaseUser),
        ));
        $this->defaultConnection->executeStatement(sprintf(
            'GRANT USAGE ON SCHEMA public TO %s',
            $this->defaultConnection->quoteSingleIdentifier($this->mcpDatabaseUser),
        ));
        $this->defaultConnection->executeStatement(sprintf(
            'REVOKE ALL PRIVILEGES ON ALL TABLES IN SCHEMA public FROM %s',
            $this->defaultConnection->quoteSingleIdentifier($this->mcpDatabaseUser),
        ));
        $this->defaultConnection->executeStatement(sprintf(
            'GRANT SELECT ON ALL TABLES IN SCHEMA public TO %s',
            $this->defaultConnection->quoteSingleIdentifier($this->mcpDatabaseUser),
        ));
    }

    protected function ensureDefaultPrivileges(): void
    {
        $this->defaultConnection->executeStatement(sprintf(
            'ALTER DEFAULT PRIVILEGES FOR ROLE %s IN SCHEMA public REVOKE ALL ON TABLES FROM %s',
            $this->defaultConnection->quoteSingleIdentifier($this->databaseUser),
            $this->defaultConnection->quoteSingleIdentifier($this->mcpDatabaseUser),
        ));
        $this->defaultConnection->executeStatement(sprintf(
            'ALTER DEFAULT PRIVILEGES FOR ROLE %s IN SCHEMA public GRANT SELECT ON TABLES TO %s',
            $this->defaultConnection->quoteSingleIdentifier($this->databaseUser),
            $this->defaultConnection->quoteSingleIdentifier($this->mcpDatabaseUser),
        ));
    }

    protected function mcpUserExists(): bool
    {
        return (bool)$this->defaultConnection->fetchOne(
            'SELECT 1 FROM pg_roles WHERE rolname = ?',
            [$this->mcpDatabaseUser],
        );
    }
}

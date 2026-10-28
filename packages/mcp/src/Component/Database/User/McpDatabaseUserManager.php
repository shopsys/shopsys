<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\User;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use RuntimeException;
use Shopsys\McpBundle\Component\Database\View\McpDatabaseViewsManager;

/**
 * Prepares the MCP database role for local development and CI, where the application creates the whole database
 * (it runs when the database schema is created). On staging and production servers, the role is provisioned by the hosting.
 *
 * The role mirrors the recommended setup: it may only connect, and it reads the database only through the views
 * in the MCP schema, which McpDatabaseViewsManager grants it. It may look up objects in the public schema, but has
 * no access to the tables there, so the database itself keeps MCP queries away from hidden columns, not only the SQL validator.
 */
class McpDatabaseUserManager
{
    public const int CONNECTION_LIMIT = 10;
    public const string WORK_MEM = '4MB';
    public const string TEMP_FILE_LIMIT = '256MB';

    public function __construct(
        protected readonly Connection $defaultConnection,
        protected readonly string $databaseUser,
        protected readonly string $mcpDatabaseUser,
        protected readonly string $mcpDatabasePassword,
        protected readonly McpDatabaseRoleChecker $mcpDatabaseRoleChecker,
    ) {
    }

    public function ensureMcpUser(): string
    {
        $this->validateConfiguration();

        try {
            $this->defaultConnection->transactional(function (): void {
                $this->ensureRole();
                $this->ensureRoleSettings();
                $this->ensureDatabasePrivileges();
                $this->ensureSchemaPrivileges();
                $this->ensureDefaultPrivileges();
            });
        } catch (Exception $exception) {
            throw new RuntimeException(
                sprintf(
                    'MCP database role "%s" could not be prepared. Ensure the active database connection can manage roles and grants.',
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
            throw new RuntimeException('MCP_DATABASE_USER must differ from the application database owner so MCP can use a dedicated role.');
        }

        if (trim($this->mcpDatabasePassword) === '') {
            throw new RuntimeException('MCP_DATABASE_PASSWORD must not be empty.');
        }
    }

    protected function ensureRole(): void
    {
        $roleStatementAction = $this->mcpDatabaseRoleChecker->exists() ? 'ALTER' : 'CREATE';

        $this->defaultConnection->executeStatement(sprintf(
            '%s ROLE %s WITH LOGIN PASSWORD %s CONNECTION LIMIT %d NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION NOBYPASSRLS',
            $roleStatementAction,
            $this->defaultConnection->quoteSingleIdentifier($this->mcpDatabaseUser),
            $this->defaultConnection->quote($this->mcpDatabasePassword),
            self::CONNECTION_LIMIT,
        ));
    }

    /**
     * The MCP connection sets the search path and the read-only mode per session, the role defaults keep them in place
     * when a connection pooler resets the session. The statement timeout does not limit memory, so the role settings
     * limit sorts, hashes and temporary files as well.
     */
    protected function ensureRoleSettings(): void
    {
        $roleSettingValuesIndexedByNames = [
            'search_path' => $this->defaultConnection->quoteSingleIdentifier(McpDatabaseViewsManager::VIEWS_SCHEMA_NAME),
            'default_transaction_read_only' => 'on',
            'work_mem' => $this->defaultConnection->quote(self::WORK_MEM),
            'temp_file_limit' => $this->defaultConnection->quote(self::TEMP_FILE_LIMIT),
        ];

        foreach ($roleSettingValuesIndexedByNames as $roleSettingName => $roleSettingValue) {
            $this->defaultConnection->executeStatement(sprintf(
                'ALTER ROLE %s SET %s = %s',
                $this->defaultConnection->quoteSingleIdentifier($this->mcpDatabaseUser),
                $roleSettingName,
                $roleSettingValue,
            ));
        }
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
            'REVOKE ALL PRIVILEGES ON SCHEMA public FROM %s',
            $this->defaultConnection->quoteSingleIdentifier($this->mcpDatabaseUser),
        ));
        $this->defaultConnection->executeStatement(sprintf(
            'REVOKE ALL PRIVILEGES ON ALL TABLES IN SCHEMA public FROM %s',
            $this->defaultConnection->quoteSingleIdentifier($this->mcpDatabaseUser),
        ));
        // the planner inlines the SQL functions of expression indexes (e.g. normalized()) with the privileges of the querying role,
        // so queries on the views of such tables need to look the functions up, the tables stay inaccessible
        $this->defaultConnection->executeStatement(sprintf(
            'GRANT USAGE ON SCHEMA public TO %s',
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
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\View;

use Doctrine\DBAL\Connection;
use Shopsys\McpBundle\Component\Database\Schema\AllowedDatabaseColumnsProvider;
use Shopsys\McpBundle\Component\Database\User\McpDatabaseRoleChecker;
use Shopsys\McpBundle\Component\Database\View\Exception\McpDatabaseRoleNotFoundException;

/**
 * MCP queries run against views in a dedicated schema that contain only the exposed columns of the exposed tables,
 * so hidden columns do not exist for unqualified names, whatever way of referencing a column a query uses.
 * Whether the MCP role can also read the tables in the public schema directly depends on how the role is provisioned,
 * the recommended setup grants it nothing there (see McpDatabaseUserManager), the SQL validator rejects such references as well.
 */
class McpDatabaseViewsManager
{
    public const string VIEWS_SCHEMA_NAME = 'mcp';

    public function __construct(
        protected readonly Connection $defaultConnection,
        protected readonly AllowedDatabaseColumnsProvider $allowedDatabaseColumnsProvider,
        protected readonly McpDatabaseRoleChecker $mcpDatabaseRoleChecker,
        protected readonly string $mcpDatabaseUser,
    ) {
    }

    /**
     * Views depend on the columns they select, PostgreSQL would not allow migrations to drop or alter them.
     */
    public function dropViews(): void
    {
        $this->defaultConnection->executeStatement(sprintf(
            'DROP SCHEMA IF EXISTS %s CASCADE',
            $this->defaultConnection->quoteSingleIdentifier(self::VIEWS_SCHEMA_NAME),
        ));
    }

    public function recreateViews(): void
    {
        if (!$this->mcpDatabaseRoleChecker->exists()) {
            throw new McpDatabaseRoleNotFoundException($this->mcpDatabaseUser);
        }

        $this->dropViews();

        $viewsSchemaName = $this->defaultConnection->quoteSingleIdentifier(self::VIEWS_SCHEMA_NAME);
        $mcpDatabaseUser = $this->defaultConnection->quoteSingleIdentifier($this->mcpDatabaseUser);

        $this->defaultConnection->executeStatement(sprintf('CREATE SCHEMA %s', $viewsSchemaName));

        foreach ($this->getExposedColumnNamesIndexedByTableNames() as $tableName => $columnNames) {
            $quotedTableName = $this->defaultConnection->quoteSingleIdentifier($tableName);

            $this->defaultConnection->executeStatement(sprintf(
                'CREATE VIEW %s.%s AS SELECT %s FROM public.%s',
                $viewsSchemaName,
                $quotedTableName,
                implode(', ', array_map($this->defaultConnection->quoteSingleIdentifier(...), $columnNames)),
                $quotedTableName,
            ));
        }

        $this->defaultConnection->executeStatement(sprintf('GRANT USAGE ON SCHEMA %s TO %s', $viewsSchemaName, $mcpDatabaseUser));
        $this->defaultConnection->executeStatement(sprintf('GRANT SELECT ON ALL TABLES IN SCHEMA %s TO %s', $viewsSchemaName, $mcpDatabaseUser));
    }

    /**
     * The live columns are read through the default connection, so the columns added by the running migration transaction are visible.
     *
     * @return array<string, list<string>>
     */
    protected function getExposedColumnNamesIndexedByTableNames(): array
    {
        $allowedColumnsSetIndexedByTableNames = $this->allowedDatabaseColumnsProvider->getAllAllowedColumnsSetIndexedByTableNames();
        $liveColumns = $this->defaultConnection->fetchAllAssociative(
            'SELECT table_name, column_name FROM information_schema.columns WHERE table_schema = \'public\' ORDER BY table_name, ordinal_position',
        );
        $exposedColumnNamesIndexedByTableNames = [];

        foreach ($liveColumns as $liveColumn) {
            $tableName = (string)$liveColumn['table_name'];
            $columnName = (string)$liveColumn['column_name'];

            if (array_key_exists($columnName, $allowedColumnsSetIndexedByTableNames[$tableName] ?? [])) {
                $exposedColumnNamesIndexedByTableNames[$tableName][] = $columnName;
            }
        }

        return $exposedColumnNamesIndexedByTableNames;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\PostDeploy\Task\Exception;

use RuntimeException;

class McpDatabaseRoleCanReadPublicSchemaTablesException extends RuntimeException
{
    protected const int LISTED_TABLE_NAMES_LIMIT = 5;

    /**
     * @param list<string> $readableTableNames
     */
    public function __construct(
        string $mcpDatabaseUser,
        string $databaseUser,
        array $readableTableNames,
        bool $hasDefaultPrivileges,
    ) {
        parent::__construct(sprintf(
            'MCP database role "%s" (MCP_DATABASE_USER) %s, so the database does not keep MCP queries away from the hidden columns. Run as a database superuser: REVOKE ALL PRIVILEGES ON ALL TABLES IN SCHEMA public FROM "%s"; ALTER DEFAULT PRIVILEGES FOR ROLE "%s" IN SCHEMA public REVOKE ALL ON TABLES FROM "%s"; and revoke its membership in groups with access to the tables, e.g. REVOKE pg_read_all_data FROM "%s"; see the MCP server documentation.',
            $mcpDatabaseUser,
            static::describePrivileges($readableTableNames, $hasDefaultPrivileges),
            $mcpDatabaseUser,
            $databaseUser,
            $mcpDatabaseUser,
            $mcpDatabaseUser,
        ));
    }

    /**
     * @param list<string> $readableTableNames
     */
    protected static function describePrivileges(array $readableTableNames, bool $hasDefaultPrivileges): string
    {
        $privilegeDescriptions = [];

        if ($readableTableNames !== []) {
            $readableTablesCount = count($readableTableNames);
            $privilegeDescriptions[] = sprintf(
                'can read %d %s in the public schema (%s%s)',
                $readableTablesCount,
                $readableTablesCount === 1 ? 'table' : 'tables',
                implode(', ', array_slice($readableTableNames, 0, static::LISTED_TABLE_NAMES_LIMIT)),
                $readableTablesCount > static::LISTED_TABLE_NAMES_LIMIT ? sprintf(', and %d more', $readableTablesCount - static::LISTED_TABLE_NAMES_LIMIT) : '',
            );
        }

        if ($hasDefaultPrivileges) {
            $privilegeDescriptions[] = 'has default privileges on the tables created in the public schema';
        }

        return implode(' and ', $privilegeDescriptions);
    }
}

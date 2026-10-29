<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\View;

use Doctrine\DBAL\Connection;

class McpDatabaseViewsRepository
{
    public function __construct(
        protected readonly Connection $defaultConnection,
    ) {
    }

    /**
     * @return array<string, list<string>>
     */
    public function getColumnNamesIndexedByTableNames(string $schemaName): array
    {
        $schemaColumns = $this->defaultConnection->fetchAllAssociative(
            'SELECT table_name, column_name FROM information_schema.columns WHERE table_schema = ? ORDER BY table_name, ordinal_position',
            [$schemaName],
        );
        $columnNamesIndexedByTableNames = [];

        foreach ($schemaColumns as $schemaColumn) {
            $columnNamesIndexedByTableNames[(string)$schemaColumn['table_name']][] = (string)$schemaColumn['column_name'];
        }

        return $columnNamesIndexedByTableNames;
    }

    /**
     * @param list<string> $columnNames
     */
    public function createView(
        string $viewsSchemaName,
        string $tableSchemaName,
        string $tableName,
        array $columnNames,
    ): void {
        $quotedTableName = $this->defaultConnection->quoteSingleIdentifier($tableName);

        $this->defaultConnection->executeStatement(sprintf(
            'CREATE VIEW %s.%s AS SELECT %s FROM %s.%s',
            $this->defaultConnection->quoteSingleIdentifier($viewsSchemaName),
            $quotedTableName,
            implode(', ', array_map($this->defaultConnection->quoteSingleIdentifier(...), $columnNames)),
            $this->defaultConnection->quoteSingleIdentifier($tableSchemaName),
            $quotedTableName,
        ));
    }

    public function grantSelectOnViews(string $viewsSchemaName, string $roleName): void
    {
        $quotedViewsSchemaName = $this->defaultConnection->quoteSingleIdentifier($viewsSchemaName);
        $quotedRoleName = $this->defaultConnection->quoteSingleIdentifier($roleName);

        $this->defaultConnection->executeStatement(sprintf('GRANT USAGE ON SCHEMA %s TO %s', $quotedViewsSchemaName, $quotedRoleName));
        $this->defaultConnection->executeStatement(sprintf('GRANT SELECT ON ALL TABLES IN SCHEMA %s TO %s', $quotedViewsSchemaName, $quotedRoleName));
    }
}

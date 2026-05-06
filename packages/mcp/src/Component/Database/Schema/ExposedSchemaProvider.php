<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\Table;
use JsonException;
use RuntimeException;
use Shopsys\McpBundle\Command\GenerateSchemaCommand;

/**
 * @phpstan-type SchemaColumnArray array{dataType: string, nullable: bool}
 * @phpstan-type SchemaForeignKeyArray array{
 *     columnNames: array<string>,
 *     referencedTable: string,
 *     referencedColumnNames: array<string>,
 *     onDelete: string
 * }
 * @phpstan-type SchemaTableArray array{
 *     primaryKey: array<string>,
 *     columns: array<string, SchemaColumnArray>,
 *     foreignKeys: array<int, SchemaForeignKeyArray>
 * }
 */
class ExposedSchemaProvider
{
    protected const string SCHEMA_FILE_NAME = 'mcp-schema.json';

    /**
     * @var array<string, SchemaTableArray>|null
     */
    protected ?array $storedExposedSchema = null;

    public function __construct(
        protected readonly Connection $mcpConnection,
        protected readonly AllowedDatabaseTablesProvider $allowedDatabaseTablesProvider,
        protected readonly AllowedDatabaseColumnsProvider $allowedDatabaseColumnsProvider,
        protected readonly SchemaNameNormalizer $schemaNameNormalizer,
        protected readonly string $cacheDir,
    ) {
    }

    /**
     * @param array<string> $tableNames
     * @return array<string, SchemaTableArray>
     */
    public function getExposedSchema(array $tableNames): array
    {
        $storedExposedSchema = $this->getStoredExposedSchema();

        if ($tableNames === []) {
            return $storedExposedSchema;
        }

        $filteredTables = [];

        foreach ($tableNames as $tableName) {
            if (array_key_exists($tableName, $storedExposedSchema)) {
                $filteredTables[$tableName] = $storedExposedSchema[$tableName];
            }
        }

        return $filteredTables;
    }

    /**
     * @return array<string>
     */
    public function getExposedTableNames(): array
    {
        return array_keys($this->getStoredExposedSchema());
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public function getAllowedColumnsSetIndexedByTableNames(): array
    {
        $allowedColumnsSetIndexedByTableNames = [];

        foreach ($this->getStoredExposedSchema() as $tableName => $tableSchema) {
            $allowedColumnsSetIndexedByTableNames[$tableName] = array_fill_keys(
                array_keys($tableSchema['columns']),
                true,
            );
        }

        return $allowedColumnsSetIndexedByTableNames;
    }

    public function generateExposedSchemaJson(): string
    {
        return json_encode(
            $this->generateLiveExposedSchema(),
            JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ) . "\n";
    }

    public function getSchemaFilePath(): string
    {
        return $this->cacheDir . '/' . self::SCHEMA_FILE_NAME;
    }

    /**
     * @return array<string, SchemaTableArray>
     */
    protected function getStoredExposedSchema(): array
    {
        if ($this->storedExposedSchema !== null) {
            return $this->storedExposedSchema;
        }

        $schemaFilePath = $this->getSchemaFilePath();

        if (!is_file($schemaFilePath)) {
            throw new RuntimeException(sprintf(
                'Generated MCP schema file is missing: %s. Run "php bin/console %s".',
                $schemaFilePath,
                GenerateSchemaCommand::COMMAND_NAME,
            ));
        }

        $schemaJson = file_get_contents($schemaFilePath);

        if ($schemaJson === false) {
            throw new RuntimeException(sprintf('Generated MCP schema file could not be read: %s.', $schemaFilePath));
        }

        try {
            /** @var array<string, SchemaTableArray> $storedExposedSchema */
            $storedExposedSchema = json_decode($schemaJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new RuntimeException(
                sprintf('Generated MCP schema file is invalid JSON: %s.', $schemaFilePath),
                previous: $jsonException,
            );
        }

        $this->storedExposedSchema = $storedExposedSchema;

        return $this->storedExposedSchema;
    }

    /**
     * @return array<string, SchemaTableArray>
     */
    protected function generateLiveExposedSchema(): array
    {
        $allowedColumnsSetIndexedByTableNames = $this->allowedDatabaseColumnsProvider->getAllAllowedColumnsSetIndexedByTableNames();
        $tableSchemas = $this->getLiveTableSchemas(array_keys($allowedColumnsSetIndexedByTableNames));
        $filteredTables = [];

        foreach ($tableSchemas as $tableName => $tableSchema) {
            $filteredTable = $this->filterTable(
                $tableName,
                $tableSchema,
                $tableSchemas,
                $allowedColumnsSetIndexedByTableNames,
            );

            if ($filteredTable['columns'] === []) {
                continue;
            }

            $filteredTables[$tableName] = $filteredTable;
        }

        return $filteredTables;
    }

    /**
     * @param array<string> $allowedTables
     * @return array<string, SchemaTableArray>
     */
    protected function getLiveTableSchemas(array $allowedTables): array
    {
        $schemaManager = $this->mcpConnection->createSchemaManager();
        $liveTablesIndexedByTableNames = [];
        $tableSchemas = [];

        foreach ($schemaManager->introspectTables() as $table) {
            $normalizedTableName = $this->schemaNameNormalizer->normalizeTableName($table->getObjectName());
            $liveTablesIndexedByTableNames[$normalizedTableName] = $table;
        }

        foreach ($allowedTables as $tableName) {
            $table = $liveTablesIndexedByTableNames[$tableName] ?? null;

            if ($table === null) {
                continue;
            }

            $primaryKeyConstraint = $table->getPrimaryKeyConstraint();
            $tableSchemas[$tableName] = [
                'primaryKey' => $primaryKeyConstraint !== null
                    ? array_map(
                        fn (UnqualifiedName $columnName): string => $this->schemaNameNormalizer->normalizeColumnName($columnName),
                        $primaryKeyConstraint->getColumnNames(),
                    )
                    : [],
                'columns' => $this->getColumns($table),
                'foreignKeys' => $this->getForeignKeys($table),
            ];
        }

        ksort($tableSchemas);

        return $tableSchemas;
    }

    /**
     * @param array<string, SchemaTableArray> $tableSchemas
     * @param array<string, array<string, bool>> $allowedColumnsSetIndexedByTableNames
     */
    protected function filterTable(
        string $tableName,
        array $tableSchema,
        array $tableSchemas,
        array $allowedColumnsSetIndexedByTableNames,
    ): array {
        $columns = array_filter(
            $tableSchema['columns'],
            fn (array $column, string $columnName): bool => $this->isColumnAllowed(
                $tableName,
                $columnName,
                $allowedColumnsSetIndexedByTableNames,
            ),
            ARRAY_FILTER_USE_BOTH,
        );
        $primaryKey = array_values(array_filter(
            $tableSchema['primaryKey'],
            fn (string $columnName): bool => $this->isColumnAllowed(
                $tableName,
                $columnName,
                $allowedColumnsSetIndexedByTableNames,
            ),
        ));
        $foreignKeys = array_values(array_filter(
            $tableSchema['foreignKeys'],
            fn (array $foreignKey): bool => $this->isForeignKeyAllowed(
                $tableName,
                $foreignKey,
                $tableSchemas,
                $allowedColumnsSetIndexedByTableNames,
            ),
        ));

        return [
            'primaryKey' => $primaryKey,
            'columns' => $columns,
            'foreignKeys' => $foreignKeys,
        ];
    }

    /**
     * @param SchemaForeignKeyArray $foreignKey
     * @param array<string, SchemaTableArray> $tableSchemas
     * @param array<string, array<string, bool>> $allowedColumnsSetIndexedByTableNames
     */
    protected function isForeignKeyAllowed(
        string $tableName,
        array $foreignKey,
        array $tableSchemas,
        array $allowedColumnsSetIndexedByTableNames,
    ): bool {
        return array_key_exists($foreignKey['referencedTable'], $tableSchemas)
            && !$this->containsNotAllowedColumn($tableName, $foreignKey['columnNames'], $allowedColumnsSetIndexedByTableNames)
            && !$this->containsNotAllowedColumn($foreignKey['referencedTable'], $foreignKey['referencedColumnNames'], $allowedColumnsSetIndexedByTableNames);
    }

    /**
     * @param array<string, array<string, bool>> $allowedColumnsSetIndexedByTableNames
     */
    protected function isColumnAllowed(
        string $tableName,
        string $columnName,
        array $allowedColumnsSetIndexedByTableNames,
    ): bool {
        return array_key_exists($columnName, $allowedColumnsSetIndexedByTableNames[$tableName] ?? []);
    }

    /**
     * @param array<string> $columnNames
     * @param array<string, array<string, bool>> $allowedColumnsSetIndexedByTableNames
     */
    protected function containsNotAllowedColumn(
        string $tableName,
        array $columnNames,
        array $allowedColumnsSetIndexedByTableNames,
    ): bool {
        foreach ($columnNames as $columnName) {
            if (!$this->isColumnAllowed($tableName, $columnName, $allowedColumnsSetIndexedByTableNames)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, SchemaColumnArray>
     */
    protected function getColumns(Table $table): array
    {
        $columns = [];

        foreach ($table->getColumns() as $column) {
            $columns[$this->schemaNameNormalizer->normalizeColumnName($column->getObjectName())] = [
                'dataType' => $this->getColumnDataType($column),
                'nullable' => !$column->getNotnull(),
            ];
        }

        ksort($columns);

        return $columns;
    }

    /**
     * @return array<int, SchemaForeignKeyArray>
     */
    protected function getForeignKeys(Table $table): array
    {
        $foreignKeys = [];

        foreach ($table->getForeignKeys() as $foreignKey) {
            $foreignKeys[] = [
                'columnNames' => array_map(
                    fn (UnqualifiedName $columnName): string => $this->schemaNameNormalizer->normalizeColumnName($columnName),
                    $foreignKey->getReferencingColumnNames(),
                ),
                'referencedTable' => $this->schemaNameNormalizer->normalizeTableName($foreignKey->getReferencedTableName()),
                'referencedColumnNames' => array_map(
                    fn (UnqualifiedName $columnName): string => $this->schemaNameNormalizer->normalizeColumnName($columnName),
                    $foreignKey->getReferencedColumnNames(),
                ),
                'onDelete' => $this->getForeignKeyOnDelete($foreignKey),
            ];
        }

        return $foreignKeys;
    }

    protected function getColumnDataType(Column $column): string
    {
        return $column->getType()->getSQLDeclaration(
            $column->toArray(),
            $this->mcpConnection->getDatabasePlatform(),
        );
    }

    protected function getForeignKeyOnDelete(ForeignKeyConstraint $foreignKey): string
    {
        return $foreignKey->getOnDeleteAction()->toSQL();
    }
}

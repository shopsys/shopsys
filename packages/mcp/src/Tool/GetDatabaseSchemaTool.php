<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Tool;

use Mcp\Capability\Attribute\McpTool;
use Shopsys\McpBundle\Component\Database\Schema\ExposedSchemaProvider;
use Shopsys\McpBundle\Component\Logger\McpToolCallLogger;

/**
 * @phpstan-import-type SchemaTableArray from \Shopsys\McpBundle\Component\Database\Schema\ExposedSchemaProvider
 */
class GetDatabaseSchemaTool
{
    protected const string TOOL_NAME = 'getDatabaseSchema';

    public function __construct(
        protected readonly ExposedSchemaProvider $exposedSchemaProvider,
        protected readonly McpToolCallLogger $mcpToolCallLogger,
    ) {
    }

    /**
     * @param array<string> $tableNames
     * @return array<string, SchemaTableArray>
     */
    #[McpTool(
        name: self::TOOL_NAME,
        description: 'Returns detailed PostgreSQL-oriented schema for selected exposed tables. When planning joins, request all related tables in one call so foreign key metadata between them is included in the response.',
        outputSchema: [
            'type' => 'object',
            'additionalProperties' => [
                'type' => 'object',
                'properties' => [
                    'primaryKey' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                        ],
                    ],
                    'columns' => [
                        'type' => 'object',
                        'additionalProperties' => [
                            'type' => 'object',
                            'properties' => [
                                'dataType' => [
                                    'type' => 'string',
                                ],
                                'nullable' => [
                                    'type' => 'boolean',
                                ],
                            ],
                            'required' => ['dataType', 'nullable'],
                        ],
                    ],
                    'foreignKeys' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'columnNames' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                ],
                                'referencedTable' => ['type' => 'string'],
                                'referencedColumnNames' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                ],
                                'onDelete' => ['type' => 'string'],
                            ],
                            'required' => ['columnNames', 'referencedTable', 'referencedColumnNames', 'onDelete'],
                        ],
                    ],
                ],
                'required' => ['primaryKey', 'columns', 'foreignKeys'],
            ],
        ],
    )]
    public function getDatabaseSchema(array $tableNames): array
    {
        $startedAt = microtime(true);
        $inputContext = [
            'requested_table_count' => count($tableNames),
            'requested_table_names' => $tableNames,
        ];
        $tables = $this->exposedSchemaProvider->getExposedSchema($tableNames);

        $this->mcpToolCallLogger->logSuccess(static::TOOL_NAME, $inputContext, [
            'returned_table_count' => count($tables),
        ], $startedAt);

        return $tables;
    }
}

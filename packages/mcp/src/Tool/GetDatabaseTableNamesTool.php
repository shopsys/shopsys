<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Tool;

use Mcp\Capability\Attribute\McpTool;
use Shopsys\McpBundle\Component\Database\Schema\ExposedSchemaProvider;
use Shopsys\McpBundle\Component\Logger\McpToolCallLogger;

class GetDatabaseTableNamesTool
{
    protected const string TOOL_NAME = 'getDatabaseTableNames';

    public function __construct(
        protected readonly ExposedSchemaProvider $exposedSchemaProvider,
        protected readonly McpToolCallLogger $mcpToolCallLogger,
    ) {
    }

    /**
     * @return array{tableNames: array<string>}
     */
    #[McpTool(
        name: self::TOOL_NAME,
        description: 'Returns names of exposed database tables. Call this first to discover relevant tables before requesting detailed schema.',
        outputSchema: [
            'type' => 'object',
            'properties' => [
                'tableNames' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                ],
            ],
            'required' => ['tableNames'],
        ],
    )]
    public function getDatabaseTableNames(): array
    {
        $startedAt = microtime(true);
        $tableNames = $this->exposedSchemaProvider->getExposedTableNames();

        $this->mcpToolCallLogger->logSuccess(static::TOOL_NAME, [], [
            'returned_table_count' => count($tableNames),
        ], $startedAt);

        return [
            'tableNames' => $tableNames,
        ];
    }
}

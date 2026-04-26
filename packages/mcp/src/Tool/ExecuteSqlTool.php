<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Tool;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Exception\ToolCallException;
use Shopsys\McpBundle\Component\Database\Query\SqlExecutor;

class ExecuteSqlTool
{
    protected const string TOOL_NAME = 'executeSql';

    public function __construct(protected readonly SqlExecutor $sqlExecutor)
    {
    }

    /**
     * @return array{
     *     columnNames: array<string>,
     *     rows: array<int, array<string, string|int|float|bool|null>>,
     *     rowCount: int,
     *     durationMs: float
     * }
     */
    #[McpTool(
        name: self::TOOL_NAME,
        description: 'Executes SQL through the MCP database connection and returns normalized result rows. Always include a top-level LIMIT to keep the result bounded.',
        outputSchema: [
            'type' => 'object',
            'properties' => [
                'columnNames' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                ],
                'rows' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => [
                            'type' => ['string', 'number', 'boolean', 'null'],
                        ],
                    ],
                ],
                'rowCount' => ['type' => 'integer'],
                'durationMs' => ['type' => 'number'],
            ],
            'required' => ['columnNames', 'rows', 'rowCount', 'durationMs'],
        ],
    )]
    public function executeSql(string $sql): array
    {
        $sqlExecutionResult = $this->sqlExecutor->execute($sql);

        if (!$sqlExecutionResult->isValid) {
            throw new ToolCallException($sqlExecutionResult->errorMessage ?? 'SQL query is invalid.');
        }

        return $sqlExecutionResult->data ?? [];
    }
}

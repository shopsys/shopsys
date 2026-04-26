<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Result;

class SqlExecutor
{
    protected const string ERROR_EXECUTION_FAILED = 'SQL execution failed.';

    public function __construct(
        protected readonly Connection $mcpConnection,
        protected readonly QueryResultMcpNormalizer $queryResultMcpNormalizer,
        protected readonly SqlQueryValidator $sqlQueryValidator,
        protected readonly int $maxReturnedRows,
    ) {
    }

    public function execute(string $sql): SqlExecutionResult
    {
        $sqlQueryValidationResult = $this->sqlQueryValidator->validate($sql);

        if (!$sqlQueryValidationResult->isValid || $sqlQueryValidationResult->singleStatementSql === null) {
            return SqlExecutionResult::createInvalid($sqlQueryValidationResult->errorMessage ?? 'SQL query is invalid.');
        }

        $singleStatementSql = $sqlQueryValidationResult->singleStatementSql;

        $startedAt = microtime(true);

        try {
            $rows = $this->executeQuery($singleStatementSql);
        } catch (DbalException) {
            return SqlExecutionResult::createInvalid(self::ERROR_EXECUTION_FAILED);
        }

        return SqlExecutionResult::createValid([
            'columnNames' => $rows !== [] ? array_keys(reset($rows)) : [],
            'rows' => $rows,
            'rowCount' => count($rows),
            'durationMs' => round((microtime(true) - $startedAt) * 1000, 3),
        ]);
    }

    /**
     * @return array<int, array<string, string|int|float|bool|null>>
     */
    protected function executeQuery(string $sql): array
    {
        $result = $this->mcpConnection->executeQuery($sql);

        try {
            return $this->queryResultMcpNormalizer->normalizeRows($this->fetchLimitedRows($result));
        } finally {
            $result->free();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchLimitedRows(Result $result): array
    {
        $rows = [];

        for ($fetchedRowCount = 0; $fetchedRowCount < $this->maxReturnedRows; $fetchedRowCount++) {
            $row = $result->fetchAssociative();

            if ($row === false) {
                break;
            }

            $rows[] = $row;
        }

        return $rows;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DbalDriverException;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Result;

class SqlExecutor
{
    protected const string ERROR_EXECUTION_FAILED = 'SQL execution failed.';
    protected const string ERROR_SYSTEM_UNAVAILABLE = 'System is temporarily unavailable, try it later.';
    public const string POSTGRES_TOO_MANY_CONNECTIONS_SQLSTATE = '53300';
    public const string POSTGRES_CANNOT_CONNECT_NOW_SQLSTATE = '57P03';
    protected const string POSTGRES_CONNECTION_EXCEPTION_SQLSTATE_PREFIX = '08';

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
        } catch (DbalException $exception) {
            if ($this->isSystemUnavailableException($exception)) {
                return SqlExecutionResult::createInvalid(self::ERROR_SYSTEM_UNAVAILABLE);
            }

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

    protected function isSystemUnavailableException(DbalException $exception): bool
    {
        for ($current = $exception; $current !== null; $current = $current->getPrevious()) {
            if (!$current instanceof DbalDriverException) {
                continue;
            }

            $sqlState = $current->getSQLState();

            if ($sqlState === null) {
                continue;
            }

            if (
                str_starts_with($sqlState, self::POSTGRES_CONNECTION_EXCEPTION_SQLSTATE_PREFIX)
                || $sqlState === self::POSTGRES_TOO_MANY_CONNECTIONS_SQLSTATE
                || $sqlState === self::POSTGRES_CANNOT_CONNECT_NOW_SQLSTATE
            ) {
                return true;
            }
        }

        return false;
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

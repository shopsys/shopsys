<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DbalDriverException;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Result;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;

class SqlExecutor
{
    protected const string ERROR_EXECUTION_FAILED = 'SQL execution failed.';
    protected const string ERROR_SYSTEM_UNAVAILABLE = 'System is temporarily unavailable, try it later.';
    public const string ERROR_UNDEFINED_COLUMN = 'The SQL query references a column that does not exist or is not exposed through MCP. Use getDatabaseSchema to see the exposed columns.';
    public const string ERROR_UNDEFINED_TABLE = 'The SQL query references a table that does not exist. Use getDatabaseTableNames to see the exposed tables.';
    public const string POSTGRES_TOO_MANY_CONNECTIONS_SQLSTATE = '53300';
    public const string POSTGRES_CANNOT_CONNECT_NOW_SQLSTATE = '57P03';
    public const string POSTGRES_LOCK_NOT_AVAILABLE_SQLSTATE = '55P03';
    public const string POSTGRES_UNDEFINED_COLUMN_SQLSTATE = '42703';
    public const string POSTGRES_UNDEFINED_TABLE_SQLSTATE = '42P01';
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
            return ExtendedClassNameResolver::resolve(SqlExecutionResult::class)::createInvalid($sqlQueryValidationResult->errorMessage ?? 'SQL query is invalid.');
        }

        $singleStatementSql = $sqlQueryValidationResult->singleStatementSql;

        $startedAt = microtime(true);

        try {
            $rows = $this->executeQuery($singleStatementSql);
        } catch (DbalException $exception) {
            return ExtendedClassNameResolver::resolve(SqlExecutionResult::class)::createInvalid($this->getExecutionErrorMessage($exception));
        }

        return ExtendedClassNameResolver::resolve(SqlExecutionResult::class)::createValid([
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
     * The database error is never passed on, its message and hints could reveal the hidden columns.
     * Only the errors the MCP client can act upon by itself get a message of their own.
     */
    protected function getExecutionErrorMessage(DbalException $exception): string
    {
        if ($this->isSystemUnavailableException($exception)) {
            return self::ERROR_SYSTEM_UNAVAILABLE;
        }

        return match ($this->findSqlState($exception)) {
            self::POSTGRES_UNDEFINED_COLUMN_SQLSTATE => self::ERROR_UNDEFINED_COLUMN,
            self::POSTGRES_UNDEFINED_TABLE_SQLSTATE => self::ERROR_UNDEFINED_TABLE,
            default => self::ERROR_EXECUTION_FAILED,
        };
    }

    protected function isSystemUnavailableException(DbalException $exception): bool
    {
        $sqlState = $this->findSqlState($exception);

        return $sqlState !== null
            && (
                str_starts_with($sqlState, self::POSTGRES_CONNECTION_EXCEPTION_SQLSTATE_PREFIX)
                || $sqlState === self::POSTGRES_TOO_MANY_CONNECTIONS_SQLSTATE
                || $sqlState === self::POSTGRES_CANNOT_CONNECT_NOW_SQLSTATE
                || $sqlState === self::POSTGRES_LOCK_NOT_AVAILABLE_SQLSTATE
            );
    }

    protected function findSqlState(DbalException $exception): ?string
    {
        for ($current = $exception; $current !== null; $current = $current->getPrevious()) {
            if ($current instanceof DbalDriverException && $current->getSQLState() !== null) {
                return $current->getSQLState();
            }
        }

        return null;
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

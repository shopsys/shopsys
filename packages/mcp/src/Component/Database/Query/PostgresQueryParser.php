<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Query;

use Shopsys\McpBundle\Component\Database\Query\Exception\SqlQueryParsingException;
use Throwable;

class PostgresQueryParser
{
    public function parseSingleStatement(string $sql): ParsedSqlQuery
    {
        if (!function_exists('pg_query_split') || !function_exists('pg_query_parse')) {
            throw new SqlQueryParsingException('The pg_query PHP extension must be installed to validate SQL queries.');
        }

        $singleStatementSql = $this->getSingleStatementSql($sql);

        try {
            $parsedAst = json_decode(pg_query_parse($singleStatementSql), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $throwable) {
            throw new SqlQueryParsingException('The SQL query could not be parsed.', 0, $throwable);
        }

        if (
            !isset($parsedAst['stmts'][0]['stmt'])
            || !is_array($parsedAst['stmts'][0]['stmt'])
        ) {
            throw new SqlQueryParsingException('The SQL query could not be parsed.');
        }

        return new ParsedSqlQuery($singleStatementSql, $parsedAst['stmts'][0]['stmt']);
    }

    protected function getSingleStatementSql(string $sql): string
    {
        try {
            $statementSqls = array_values(array_filter(
                array_map(
                    static fn (mixed $statementSql): string => trim((string)$statementSql),
                    pg_query_split($sql),
                ),
                static fn (string $statementSql): bool => $statementSql !== '',
            ));
        } catch (Throwable $throwable) {
            throw new SqlQueryParsingException('The SQL query could not be split into statements.', 0, $throwable);
        }

        if (count($statementSqls) !== 1) {
            throw new SqlQueryParsingException('Only a single SQL statement is allowed.');
        }

        $singleStatementSql = $statementSqls[0];

        if (str_ends_with($singleStatementSql, ';')) {
            $singleStatementSql = rtrim(substr($singleStatementSql, 0, -1));
        }

        return $singleStatementSql;
    }
}

<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\McpBundle\Component\Database\Query\SqlExecutor;
use Shopsys\McpBundle\Component\Database\Query\SqlQueryValidator;
use Tests\App\Test\FunctionalTestCase;

/**
 * Hidden columns are kept away from MCP queries by the database views, not by the validator,
 * so the queries run through the whole stack: the validator, the MCP connection and the views created by the migrations.
 */
class McpDatabaseViewsTest extends FunctionalTestCase
{
    private const string SQL_EXECUTION_FAILED_ERROR_MESSAGE = 'SQL execution failed.';

    /**
     * @inject
     */
    private SqlExecutor $sqlExecutor;

    public function testExposedColumnCanBeRead(): void
    {
        $sqlExecutionResult = $this->sqlExecutor->execute('SELECT username FROM administrators ORDER BY id LIMIT 10');

        $this->assertTrue($sqlExecutionResult->isValid);
        $this->assertNotEmpty($sqlExecutionResult->data['rows'] ?? []);
    }

    #[DataProvider('provideQueriesResolvedToExposedColumnsOnly')]
    public function testQueryResolvedToExposedColumnsOnlyReturnsNoHiddenValue(string $sql): void
    {
        $sqlExecutionResult = $this->sqlExecutor->execute($sql);

        $this->assertTrue($sqlExecutionResult->isValid);

        foreach ($sqlExecutionResult->data['rows'] ?? [] as $row) {
            foreach ($row as $value) {
                $this->assertStringStartsNotWith('$2y$', (string)$value);
            }
        }
    }

    /**
     * @return iterable<string, array{sql: string}>
     */
    public static function provideQueriesResolvedToExposedColumnsOnly(): iterable
    {
        // PostgreSQL renames the columns by position, the renamed columns are the columns of the view
        yield 'column aliases of table' => [
            'sql' => 'SELECT * FROM administrators a(c1, c2, c3, username) LIMIT 10',
        ];

        // the view has no such column, so PostgreSQL resolves the name to the decoy of the outer query
        yield 'hidden column in subquery behind derived table decoy' => [
            'sql' => 'SELECT (SELECT password FROM administrators LIMIT 1) AS x FROM (SELECT 1 AS password) d LIMIT 10',
        ];
    }

    #[DataProvider('provideQueriesReadingHiddenColumns')]
    public function testHiddenColumnCannotBeRead(string $sql, string $expectedErrorMessage): void
    {
        $sqlExecutionResult = $this->sqlExecutor->execute($sql);

        $this->assertFalse($sqlExecutionResult->isValid);
        $this->assertSame($expectedErrorMessage, $sqlExecutionResult->errorMessage);
        $this->assertNull($sqlExecutionResult->data);
    }

    /**
     * The hidden columns do not exist in the views, so the database rejects the queries that pass the validator.
     *
     * @return iterable<string, array{sql: string, expectedErrorMessage: string}>
     */
    public static function provideQueriesReadingHiddenColumns(): iterable
    {
        yield 'hidden column' => [
            'sql' => 'SELECT password FROM administrators LIMIT 10',
            'expectedErrorMessage' => self::SQL_EXECUTION_FAILED_ERROR_MESSAGE,
        ];

        yield 'hidden column behind unreferenced cte decoy' => [
            'sql' => 'WITH c AS (SELECT 1 AS password) SELECT q.v FROM (SELECT password FROM administrators) q(v) LIMIT 10',
            'expectedErrorMessage' => self::SQL_EXECUTION_FAILED_ERROR_MESSAGE,
        ];

        yield 'hidden column in join condition behind decoy' => [
            'sql' => "SELECT count(*) FROM (SELECT 1 AS password) d, administrators a JOIN products p ON a.username = 'superadmin' AND password < '\$2y\$12\$q' LIMIT 10",
            'expectedErrorMessage' => self::SQL_EXECUTION_FAILED_ERROR_MESSAGE,
        ];

        yield 'hidden column inside order by expression behind output alias' => [
            'sql' => 'SELECT username AS password FROM administrators ORDER BY lower(password) LIMIT 10',
            'expectedErrorMessage' => self::SQL_EXECUTION_FAILED_ERROR_MESSAGE,
        ];

        yield 'hidden column in union branch' => [
            'sql' => 'SELECT username FROM administrators UNION ALL SELECT password FROM administrators LIMIT 10',
            'expectedErrorMessage' => self::SQL_EXECUTION_FAILED_ERROR_MESSAGE,
        ];

        yield 'not exposed table' => [
            'sql' => 'SELECT count(*) FROM administrator_mcp_tokens LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
        ];

        yield 'table of public schema' => [
            'sql' => 'SELECT password FROM public.administrators LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_SCHEMA_QUALIFIED_NAME,
        ];
    }
}

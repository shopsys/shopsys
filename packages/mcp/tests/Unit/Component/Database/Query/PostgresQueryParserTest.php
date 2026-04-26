<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Database\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\Component\Database\Query\Exception\SqlQueryParsingException;
use Shopsys\McpBundle\Component\Database\Query\PostgresQueryParser;

class PostgresQueryParserTest extends TestCase
{
    #[DataProvider('provideValidSingleStatementQueries')]
    public function testParseSingleStatementReturnsParsedQuery(string $sql, string $expectedSingleStatementSql): void
    {
        $this->skipTestIfPgQueryExtensionIsMissing();

        $postgresQueryParser = new PostgresQueryParser();

        $parsedSqlQuery = $postgresQueryParser->parseSingleStatement($sql);

        $this->assertSame($expectedSingleStatementSql, $parsedSqlQuery->singleStatementSql);
        $this->assertArrayHasKey('SelectStmt', $parsedSqlQuery->statement);
    }

    #[DataProvider('provideInvalidSingleStatementQueries')]
    public function testParseSingleStatementThrowsExceptionForInvalidQuery(string $sql, string $expectedMessage): void
    {
        $this->skipTestIfPgQueryExtensionIsMissing();

        $postgresQueryParser = new PostgresQueryParser();

        $this->expectException(SqlQueryParsingException::class);
        $this->expectExceptionMessage($expectedMessage);

        $postgresQueryParser->parseSingleStatement($sql);
    }

    /**
     * @return iterable<string, array{sql: string, expectedSingleStatementSql: string}>
     */
    public static function provideValidSingleStatementQueries(): iterable
    {
        yield 'trailing semicolon is removed' => [
            'sql' => " \nSELECT id FROM products; \n",
            'expectedSingleStatementSql' => 'SELECT id FROM products',
        ];

        yield 'string literal containing comment marker remains valid' => [
            'sql' => 'SELECT \'--\' AS value',
            'expectedSingleStatementSql' => 'SELECT \'--\' AS value',
        ];
    }

    /**
     * @return iterable<string, array{sql: string, expectedMessage: string}>
     */
    public static function provideInvalidSingleStatementQueries(): iterable
    {
        yield 'stacked statements are rejected' => [
            'sql' => 'SELECT id FROM products; SELECT id FROM product_translations',
            'expectedMessage' => 'Only a single SQL statement is allowed.',
        ];

        yield 'invalid SQL is rejected' => [
            'sql' => 'SELECT FROM',
            'expectedMessage' => 'The SQL query could not be parsed.',
        ];
    }

    private function skipTestIfPgQueryExtensionIsMissing(): void
    {
        if (!function_exists('pg_query_split') || !function_exists('pg_query_parse')) {
            $this->markTestSkipped('The pg_query PHP extension must be installed to run parser tests.');
        }
    }
}

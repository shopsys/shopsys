<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Database\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\Component\Database\Query\Exception\SqlQueryParsingException;
use Shopsys\McpBundle\Component\Database\Query\PostgresQueryParser;
use Shopsys\McpBundle\Component\Database\Query\SqlQueryValidator;
use Shopsys\McpBundle\Component\Database\Schema\ExposedSchemaProvider;

class SqlQueryValidatorTest extends TestCase
{
    private const int MAX_RETURNED_ROWS = 500;

    public function testValidateReturnsInvalidResultForEmptyQuery(): void
    {
        $sqlQueryValidator = new SqlQueryValidator(
            $this->createExposedSchemaProvider(),
            $this->createStub(PostgresQueryParser::class),
            self::MAX_RETURNED_ROWS,
        );

        $sqlQueryValidationResult = $sqlQueryValidator->validate('   ');

        $this->assertFalse($sqlQueryValidationResult->isValid);
        $this->assertNull($sqlQueryValidationResult->singleStatementSql);
        $this->assertSame(SqlQueryValidator::ERROR_EMPTY_QUERY, $sqlQueryValidationResult->errorMessage);
    }

    public function testValidateReturnsInvalidResultWhenParserThrowsException(): void
    {
        $postgresQueryParser = $this->createStub(PostgresQueryParser::class);
        $postgresQueryParser->method('parseSingleStatement')
            ->willThrowException(new SqlQueryParsingException('The SQL query could not be parsed.'));

        $sqlQueryValidator = new SqlQueryValidator(
            $this->createExposedSchemaProvider(),
            $postgresQueryParser,
            self::MAX_RETURNED_ROWS,
        );

        $sqlQueryValidationResult = $sqlQueryValidator->validate('SELECT FROM');

        $this->assertFalse($sqlQueryValidationResult->isValid);
        $this->assertNull($sqlQueryValidationResult->singleStatementSql);
        $this->assertSame('The SQL query could not be parsed.', $sqlQueryValidationResult->errorMessage);
    }

    #[DataProvider('provideValidQueries')]
    #[DataProviderExternal(SqlQueryValidatorNodeQueriesDataProvider::class, 'provideValidQueries')]
    public function testValidateReturnsValidResultForSupportedReadOnlyQueries(
        string $sql,
    ): void {
        $sqlQueryValidationResult = $this->createSqlQueryValidator()->validate($sql);

        $this->assertTrue($sqlQueryValidationResult->isValid);
        $this->assertNull($sqlQueryValidationResult->errorMessage);
    }

    #[DataProvider('provideInvalidQueries')]
    #[DataProviderExternal(SqlQueryValidatorNodeQueriesDataProvider::class, 'provideInvalidQueries')]
    public function testValidateReturnsInvalidResultForUnsupportedQueries(
        string $sql,
        string $expectedErrorMessage,
    ): void {
        $sqlQueryValidationResult = $this->createSqlQueryValidator()->validate($sql);

        $this->assertFalse($sqlQueryValidationResult->isValid);
        $this->assertNull($sqlQueryValidationResult->singleStatementSql);
        $this->assertSame($expectedErrorMessage, $sqlQueryValidationResult->errorMessage);
    }

    /**
     * @return iterable<string, array{sql: string}>
     */
    public static function provideValidQueries(): iterable
    {
        yield 'simple select against exposed table' => [
            'sql' => 'SELECT id FROM products LIMIT 10',
        ];

        yield 'select without from is allowed' => [
            'sql' => 'SELECT 1 AS value LIMIT 1',
        ];

        yield 'string literal containing comment marker is allowed' => [
            'sql' => 'SELECT \'--\' AS value LIMIT 1',
        ];

        yield 'read only cte against exposed table is allowed' => [
            'sql' => 'WITH product_ids AS (SELECT id FROM products) SELECT id FROM product_ids LIMIT 10',
        ];

        yield 'cte joined under alias is allowed' => [
            'sql' => 'WITH czk_currency AS (SELECT exchange_rate FROM currencies WHERE code = \'CZK\') SELECT ROUND(1 * czk.exchange_rate, 2) AS turnover_czk FROM orders o JOIN czk_currency czk ON TRUE LIMIT 10',
        ];

        yield 'qualified columns in join are allowed' => [
            'sql' => 'SELECT p.id, pt.translatable_id FROM products p JOIN product_translations pt ON pt.translatable_id = p.id LIMIT 10',
        ];

        yield 'column of table aliased in from clause is allowed' => [
            'sql' => 'SELECT pt.locale FROM product_translations pt LIMIT 10',
        ];

        yield 'unqualified column in join is allowed' => [
            'sql' => 'SELECT catnum FROM products p JOIN product_translations pt ON pt.translatable_id = p.id LIMIT 10',
        ];

        yield 'cte alias column list is allowed' => [
            'sql' => 'WITH c(id2) AS (SELECT id FROM products) SELECT id2 FROM c LIMIT 10',
        ];

        yield 'later cte can reference earlier cte' => [
            'sql' => 'WITH a AS (SELECT id FROM products), b AS (SELECT id FROM a) SELECT id FROM b LIMIT 10',
        ];

        yield 'derived table alias column list is allowed' => [
            'sql' => 'SELECT sub.id2 FROM (SELECT id FROM products) AS sub(id2) LIMIT 10',
        ];

        yield 'derived table is allowed' => [
            'sql' => 'SELECT sub.id FROM (SELECT id FROM administrators) sub LIMIT 10',
        ];

        yield 'order by aggregate alias is allowed' => [
            'sql' => 'SELECT customer_id, COUNT(id) AS order_count FROM orders GROUP BY customer_id ORDER BY order_count DESC, customer_id ASC LIMIT 10',
        ];

        yield 'correlated subquery is allowed' => [
            'sql' => 'SELECT id FROM products p WHERE EXISTS (SELECT 1 FROM product_translations pt WHERE pt.translatable_id = p.id) LIMIT 10',
        ];

        yield 'allowed regular function is accepted' => [
            'sql' => "SELECT COALESCE(UPPER(name), 'UNKNOWN') AS normalized_name FROM product_translations LIMIT 10",
        ];

        yield 'allowed special sql value op current date is accepted' => [
            'sql' => 'SELECT current_date LIMIT 1',
        ];

        yield 'allowed special sql value op current timestamp with precision is accepted' => [
            'sql' => 'SELECT current_timestamp(0) LIMIT 1',
        ];

        yield 'allowed special sql value op localtimestamp is accepted' => [
            'sql' => 'SELECT localtimestamp LIMIT 1',
        ];

        yield 'allowed type cast is accepted' => [
            'sql' => 'SELECT id::text FROM products LIMIT 1',
        ];

        yield 'allowed integer alias cast is accepted' => [
            'sql' => 'SELECT 1::int LIMIT 1',
        ];

        yield 'allowed cast normalized to pg catalog type is accepted' => [
            'sql' => 'SELECT CAST(id AS integer) FROM products LIMIT 1',
        ];

        yield 'allowed jsonb cast is accepted' => [
            'sql' => 'SELECT id::jsonb FROM products LIMIT 1',
        ];

        yield 'allowed uuid cast is accepted' => [
            'sql' => 'SELECT id::uuid FROM products LIMIT 1',
        ];

        yield 'allowed bytea cast is accepted' => [
            'sql' => 'SELECT id::bytea FROM products LIMIT 1',
        ];

        yield 'wildcard select is allowed because the views contain exposed columns only' => [
            'sql' => 'SELECT * FROM products LIMIT 10',
        ];

        yield 'hidden column is not checked by the validator, the views do not contain it' => [
            'sql' => 'SELECT password FROM administrators LIMIT 10',
        ];

        yield 'natural join is allowed' => [
            'sql' => 'SELECT id FROM products NATURAL JOIN orders LIMIT 10',
        ];

        yield 'union of exposed tables is allowed' => [
            'sql' => 'SELECT id FROM products UNION SELECT id FROM orders LIMIT 10',
        ];

        yield 'quoted table name of an exposed table is allowed' => [
            'sql' => 'SELECT p.id FROM "products" p LIMIT 10',
        ];
    }

    /**
     * @return iterable<string, array{sql: string, expectedErrorMessage: string}>
     */
    public static function provideInvalidQueries(): iterable
    {
        yield 'update is rejected' => [
            'sql' => 'UPDATE products SET catnum = \'x\'',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_ONLY_SELECT_SUPPORTED,
        ];

        yield 'delete cte is rejected' => [
            'sql' => 'WITH deleted_products AS (DELETE FROM products RETURNING id) SELECT id FROM deleted_products LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_ONLY_SELECT_SUPPORTED,
        ];

        yield 'recursive cte is rejected explicitly' => [
            'sql' => 'WITH RECURSIVE product_tree AS (SELECT id FROM products UNION ALL SELECT id FROM product_tree) SELECT id FROM product_tree LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_RECURSIVE_CTE_NOT_SUPPORTED,
        ];

        yield 'select into is rejected' => [
            'sql' => 'SELECT id INTO exported_products FROM products LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'missing limit is rejected' => [
            'sql' => 'SELECT id FROM products',
            'expectedErrorMessage' => sprintf(SqlQueryValidator::ERROR_LIMIT_REQUIRED_FORMAT, self::MAX_RETURNED_ROWS),
        ];

        yield 'limit above cap is rejected' => [
            'sql' => sprintf('SELECT id FROM products LIMIT %d', self::MAX_RETURNED_ROWS + 1),
            'expectedErrorMessage' => sprintf(SqlQueryValidator::ERROR_LIMIT_REQUIRED_FORMAT, self::MAX_RETURNED_ROWS),
        ];

        yield 'limit all is rejected' => [
            'sql' => 'SELECT id FROM products LIMIT ALL',
            'expectedErrorMessage' => sprintf(SqlQueryValidator::ERROR_LIMIT_REQUIRED_FORMAT, self::MAX_RETURNED_ROWS),
        ];

        yield 'nextval is rejected' => [
            'sql' => 'SELECT nextval(\'products_id_seq\') LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'select for update is rejected' => [
            'sql' => 'SELECT id FROM products LIMIT 10 FOR UPDATE',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_CONSTRUCT,
        ];

        yield 'setval is rejected' => [
            'sql' => 'SELECT setval(\'products_id_seq\', 1) LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'set config is rejected' => [
            'sql' => 'SELECT set_config(\'search_path\', \'pg_catalog\', false) LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'dblink is rejected' => [
            'sql' => 'SELECT dblink(\'dbname=shopsys\', \'SELECT 1\') LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'pg cancel backend is rejected' => [
            'sql' => 'SELECT pg_cancel_backend(123) LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'schema qualified pg cancel backend is rejected' => [
            'sql' => 'SELECT pg_catalog.pg_cancel_backend(123) LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'pg terminate backend is rejected' => [
            'sql' => 'SELECT pg_terminate_backend(123) LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'pg advisory function is rejected' => [
            'sql' => 'SELECT pg_advisory_lock(1) LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'schema qualified pg advisory function is rejected' => [
            'sql' => 'SELECT pg_catalog.pg_advisory_lock(1) LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'pg read file is rejected' => [
            'sql' => "SELECT pg_read_file('postgresql.conf', 0, 100) LIMIT 1",
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'pg read binary file is rejected' => [
            'sql' => "SELECT pg_read_binary_file('postgresql.conf', 0, 100) LIMIT 1",
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'pg ls dir is rejected' => [
            'sql' => "SELECT pg_ls_dir('.') LIMIT 1",
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'pg stat file is rejected' => [
            'sql' => "SELECT pg_stat_file('postgresql.conf') LIMIT 1",
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'pg sleep is rejected' => [
            'sql' => 'SELECT pg_sleep(1) LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'version is rejected' => [
            'sql' => 'SELECT version() LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'current database is rejected' => [
            'sql' => 'SELECT current_database() LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'current setting is rejected' => [
            'sql' => "SELECT current_setting('data_directory') LIMIT 1",
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'blocked special sql value op current user is rejected' => [
            'sql' => 'SELECT current_user LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'blocked special sql value op current role is rejected' => [
            'sql' => 'SELECT current_role LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'non allowlisted regular function is rejected' => [
            'sql' => 'SELECT md5(name) FROM product_translations LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'schema qualified safe builtin is rejected' => [
            'sql' => 'SELECT pg_catalog.round(1.23, 1) LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'schema qualified column from non public schema is rejected' => [
            'sql' => 'SELECT pg_catalog.pg_user.usename FROM products LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_SCHEMA_QUALIFIED_NAME,
        ];

        yield 'cte shadowing pg catalog table is rejected when schema qualified relation is used' => [
            'sql' => 'WITH pg_user AS (SELECT 1 AS x) SELECT pg_catalog.pg_user.usename FROM pg_catalog.pg_user LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_SCHEMA_QUALIFIED_NAME,
        ];

        yield 'non allowlisted type cast shorthand is rejected' => [
            'sql' => "SELECT 'administrators'::regclass::oid LIMIT 1",
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TYPE_NOT_ALLOWED,
        ];

        yield 'non allowlisted type cast via cast syntax is rejected' => [
            'sql' => "SELECT CAST('administrators' AS regclass)::oid LIMIT 1",
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TYPE_NOT_ALLOWED,
        ];

        yield 'non allowlisted type cast on exposed column is rejected' => [
            'sql' => 'SELECT id::text::regclass FROM products LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TYPE_NOT_ALLOWED,
        ];

        yield 'range function in from clause is rejected' => [
            'sql' => 'SELECT value FROM generate_series(1, 2) AS t(value) LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_CONSTRUCT,
        ];

        yield 'non exposed table is rejected' => [
            'sql' => 'SELECT id FROM administrator_mcp_tokens LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
        ];

        yield 'non exposed table hidden behind derived table is rejected' => [
            'sql' => 'SELECT x.secret_hash FROM (SELECT secret_hash FROM administrator_mcp_tokens) x LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
        ];

        yield 'non public schema table is rejected' => [
            'sql' => 'SELECT other.products.id FROM other.products LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_SCHEMA_QUALIFIED_NAME,
        ];

        yield 'non exposed table in union branch is rejected' => [
            'sql' => 'SELECT 0 UNION ALL SELECT count(*) FROM administrator_mcp_tokens LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
        ];

        yield 'non exposed table in where subquery is rejected' => [
            'sql' => 'SELECT id FROM products WHERE EXISTS (SELECT 1 FROM administrator_mcp_tokens) LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
        ];

        yield 'update cte is rejected' => [
            'sql' => 'WITH updated AS (UPDATE products SET catnum = \'x\' RETURNING id) SELECT id FROM updated LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_ONLY_SELECT_SUPPORTED,
        ];

        yield 'limit only inside union branch is rejected' => [
            'sql' => 'SELECT id FROM products UNION (SELECT id FROM orders LIMIT 10)',
            'expectedErrorMessage' => sprintf(SqlQueryValidator::ERROR_LIMIT_REQUIRED_FORMAT, self::MAX_RETURNED_ROWS),
        ];

        yield 'quoted public schema qualified table is rejected' => [
            'sql' => 'SELECT p.id FROM "public"."products" p LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_SCHEMA_QUALIFIED_NAME,
        ];

        yield 'database qualified table is rejected' => [
            'sql' => 'SELECT p.id FROM shopsys.public.products p LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_SCHEMA_QUALIFIED_NAME,
        ];

        yield 'schema qualified table in union branch is rejected' => [
            'sql' => 'SELECT 0 UNION ALL SELECT count(*) FROM public.administrators LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_SCHEMA_QUALIFIED_NAME,
        ];

        yield 'schema qualified table in scalar subquery is rejected' => [
            'sql' => 'SELECT (SELECT count(*) FROM public.administrators) AS c LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_SCHEMA_QUALIFIED_NAME,
        ];

        yield 'schema qualified table in cte body is rejected' => [
            'sql' => 'WITH a AS (SELECT id FROM public.administrators) SELECT id FROM a LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_SCHEMA_QUALIFIED_NAME,
        ];

        yield 'schema qualified table with tablesample is rejected' => [
            'sql' => 'SELECT count(*) FROM public.administrators TABLESAMPLE SYSTEM (100) LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_SCHEMA_QUALIFIED_NAME,
        ];

        yield 'schema qualified operator is rejected' => [
            'sql' => 'SELECT id FROM products WHERE id OPERATOR(public.=) 1 LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_SCHEMA_QUALIFIED_NAME,
        ];

        yield 'schema qualified operator of any subquery is rejected' => [
            'sql' => 'SELECT id FROM products WHERE id OPERATOR(public.=) ANY (SELECT id FROM orders) LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_SCHEMA_QUALIFIED_NAME,
        ];

        yield 'schema qualified sort operator is rejected' => [
            'sql' => 'SELECT id FROM products ORDER BY id USING OPERATOR(public.<) LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_SCHEMA_QUALIFIED_NAME,
        ];

        yield 'schema qualified collation is rejected' => [
            'sql' => 'SELECT catnum COLLATE public."C" FROM products LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_SCHEMA_QUALIFIED_NAME,
        ];

        yield 'json table is rejected' => [
            'sql' => 'SELECT a FROM JSON_TABLE(\'[1]\', \'$[*]\' COLUMNS (a int4 PATH \'$\')) jt LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_CONSTRUCT,
        ];

        yield 'unqualified pg catalog relation is rejected' => [
            'sql' => 'SELECT count(*) FROM pg_stats LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
        ];

        yield 'unqualified pg catalog relation in where subquery is rejected' => [
            'sql' => 'SELECT id FROM products WHERE EXISTS (SELECT 1 FROM pg_class) LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
        ];

        yield 'cte named as pg catalog relation is rejected' => [
            'sql' => 'WITH pg_user AS (SELECT id FROM products) SELECT id FROM pg_user LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
        ];
    }

    private function createSqlQueryValidator(): SqlQueryValidator
    {
        $this->skipTestIfPgQueryExtensionIsMissing();

        return new SqlQueryValidator(
            $this->createExposedSchemaProvider(),
            new PostgresQueryParser(),
            self::MAX_RETURNED_ROWS,
        );
    }

    private function createExposedSchemaProvider(): ExposedSchemaProvider
    {
        $exposedSchemaProvider = $this->createStub(ExposedSchemaProvider::class);
        $exposedSchemaProvider->method('getExposedTableNames')
            ->willReturn(['administrators', 'currencies', 'orders', 'products', 'product_translations']);

        return $exposedSchemaProvider;
    }

    private function skipTestIfPgQueryExtensionIsMissing(): void
    {
        if (!function_exists('pg_query_split') || !function_exists('pg_query_parse')) {
            $this->markTestSkipped('The pg_query PHP extension must be installed to run validator tests.');
        }
    }
}

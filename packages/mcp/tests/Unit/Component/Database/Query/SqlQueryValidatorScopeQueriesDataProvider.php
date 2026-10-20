<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Database\Query;

use Shopsys\McpBundle\Component\Database\Query\SqlQueryValidator;

class SqlQueryValidatorScopeQueriesDataProvider
{
    /**
     * @return iterable<string, array{sql: string}>
     */
    public static function provideValidQueries(): iterable
    {
        yield 'unqualified column in subquery without from resolves to outer relation' => [
            'sql' => 'SELECT (SELECT catnum) AS product_catnum FROM products LIMIT 10',
        ];

        yield 'unqualified column in subquery resolves to its own from clause first' => [
            'sql' => 'SELECT p.id FROM products p WHERE EXISTS (SELECT 1 FROM product_translations WHERE translatable_id = p.id) LIMIT 10',
        ];

        yield 'unqualified column resolves to outer relation through derived table with known columns' => [
            'sql' => 'SELECT p.catnum FROM products p WHERE EXISTS (SELECT 1 FROM (SELECT 1 AS x) d WHERE d.x = id) LIMIT 10',
        ];

        yield 'unreferenced cte named as exposed column does not make derived table column ambiguous' => [
            'sql' => 'WITH c AS (SELECT 1 AS username) SELECT q.v FROM (SELECT username FROM administrators) q(v) LIMIT 10',
        ];

        yield 'cte is visible in where subquery' => [
            'sql' => 'WITH product_ids AS (SELECT id FROM products) SELECT p.id FROM products p WHERE p.id IN (SELECT id FROM product_ids) LIMIT 10',
        ];

        yield 'numeric table alias is allowed' => [
            'sql' => 'SELECT "1".catnum FROM products "1" LIMIT 10',
        ];

        yield 'numeric derived table alias is allowed' => [
            'sql' => 'SELECT catnum FROM (SELECT catnum FROM products) "1" LIMIT 10',
        ];

        yield 'numeric cte name is allowed' => [
            'sql' => 'WITH "1" AS (SELECT id FROM products) SELECT id FROM "1" LIMIT 10',
        ];

        yield 'numeric alias in join using derived table is allowed' => [
            'sql' => 'SELECT p.id FROM products p JOIN (SELECT id FROM products) "2" USING (id) LIMIT 10',
        ];

        yield 'outer cte is visible in nested cte body' => [
            'sql' => 'WITH product_ids AS (SELECT id FROM products) SELECT x.id FROM (WITH nested AS (SELECT id FROM product_ids) SELECT id FROM nested) x LIMIT 10',
        ];
    }

    /**
     * @return iterable<string, array{sql: string, expectedErrorMessage: string}>
     */
    public static function provideInvalidQueries(): iterable
    {
        yield 'unreferenced cte does not satisfy hidden column in derived table' => [
            'sql' => 'WITH c AS (SELECT 1 AS password) SELECT q.v FROM (SELECT password FROM administrators) q(v) LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'unreferenced cte does not satisfy hidden column in another cte body' => [
            'sql' => 'WITH c AS (SELECT 1 AS password), d AS (SELECT password AS v FROM administrators) SELECT v FROM d LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'unreferenced cte does not satisfy hidden column in union branch inside derived table' => [
            'sql' => 'WITH c AS (SELECT 1 AS password) SELECT x.v FROM (SELECT 1 UNION ALL SELECT password FROM administrators) x(v) LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'outer derived table does not satisfy hidden column in target list subquery' => [
            'sql' => 'SELECT (SELECT password FROM administrators LIMIT 1) AS x FROM (SELECT 1 AS password) d LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'outer derived table does not satisfy hidden column in where subquery' => [
            'sql' => "SELECT d.password FROM (SELECT 1 AS password) d WHERE EXISTS (SELECT 1 FROM administrators WHERE password LIKE '\$2y\$%') LIMIT 10",
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'outer cte reference does not satisfy hidden column in target list subquery' => [
            'sql' => 'WITH c AS (SELECT 1 AS password) SELECT (SELECT password FROM administrators LIMIT 1) AS x FROM c LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'cte aliased as table name does not shadow the table in subquery' => [
            'sql' => 'WITH c AS (SELECT 1 AS password) SELECT (SELECT password FROM administrators LIMIT 1) AS x FROM c AS administrators LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'outer derived table does not satisfy hidden column two subquery levels deep' => [
            'sql' => 'SELECT (SELECT (SELECT password FROM administrators LIMIT 1) FROM products LIMIT 1) AS x FROM (SELECT 1 AS password) d LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'unqualified outer column is not resolved through subquery level with a table' => [
            'sql' => 'SELECT p.id FROM products p WHERE EXISTS (SELECT 1 FROM product_translations pt WHERE pt.translatable_id = catnum) LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'unqualified column of self joined table is ambiguous' => [
            'sql' => 'SELECT catnum FROM products p JOIN products p2 ON p2.id = p.id LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_AMBIGUOUS_UNQUALIFIED_COLUMN,
        ];
    }
}

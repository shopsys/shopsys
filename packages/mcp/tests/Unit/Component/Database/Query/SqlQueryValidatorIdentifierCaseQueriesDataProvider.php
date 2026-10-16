<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Database\Query;

use Shopsys\McpBundle\Component\Database\Query\SqlQueryValidator;

class SqlQueryValidatorIdentifierCaseQueriesDataProvider
{
    /**
     * @return iterable<string, array{sql: string}>
     */
    public static function provideValidQueries(): iterable
    {
        yield 'unquoted uppercase identifiers are folded to lowercase by the parser' => [
            'sql' => 'WITH Ids AS (SELECT ID FROM PRODUCTS) SELECT P.ID, COUNT(P.ID)::TEXT AS Total FROM IDS P GROUP BY P.ID ORDER BY TOTAL LIMIT 10',
        ];

        yield 'quoted lowercase identifiers are allowed' => [
            'sql' => 'SELECT "p"."id" FROM "products" "p" LIMIT 10',
        ];

        yield 'quoted mixed case cte and alias referenced consistently are allowed' => [
            'sql' => 'WITH "ProductIds" AS (SELECT id FROM products) SELECT "P".id FROM "ProductIds" "P" LIMIT 10',
        ];
    }

    /**
     * @return iterable<string, array{sql: string, expectedErrorMessage: string}>
     */
    public static function provideInvalidQueries(): iterable
    {
        yield 'quoted cte followed by cte with name differing only in case is rejected' => [
            'sql' => 'WITH "A" AS (SELECT password AS id FROM administrators), a AS (SELECT id FROM products) SELECT id FROM "A" LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_CTE_NAMES_COLLIDE,
        ];

        yield 'cte followed by quoted cte with name differing only in case is rejected' => [
            'sql' => 'WITH a AS (SELECT id FROM products), "A" AS (SELECT password AS id FROM administrators) SELECT id FROM "A" LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_CTE_NAMES_COLLIDE,
        ];

        yield 'duplicate cte name is rejected' => [
            'sql' => 'WITH a AS (SELECT id FROM products), a AS (SELECT id FROM products) SELECT id FROM a LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_CTE_NAMES_COLLIDE,
        ];

        yield 'quoted cte does not shadow exposed table with different case' => [
            'sql' => 'WITH "Administrators" AS (SELECT 1 AS password) SELECT password FROM administrators LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'quoted cte does not shadow non exposed table with different case' => [
            'sql' => 'WITH "Login_As_User_Exchange_Tokens" AS (SELECT 1 AS token) SELECT token FROM login_as_user_exchange_tokens LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
        ];

        yield 'quoted derived table column does not satisfy hidden column with different case' => [
            'sql' => 'SELECT password FROM administrators, (SELECT 1 AS "Password") d LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'quoted table name with different case is rejected' => [
            'sql' => 'SELECT id FROM "Products" LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
        ];

        yield 'quoted relation alias with different case is not visible unquoted' => [
            'sql' => 'SELECT p.id FROM products "P" LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNKNOWN_RELATION_ALIAS,
        ];

        yield 'quoted function name with different case is not matched against allowed functions' => [
            'sql' => 'SELECT "Lower"(catnum) FROM products LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'quoted type name with different case is not matched against allowed casts' => [
            'sql' => 'SELECT id::"TEXT" FROM products LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_DISALLOWED_CAST_TARGET,
        ];
    }
}

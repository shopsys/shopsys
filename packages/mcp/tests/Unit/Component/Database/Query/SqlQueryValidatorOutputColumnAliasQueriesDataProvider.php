<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Database\Query;

use Shopsys\McpBundle\Component\Database\Query\SqlQueryValidator;

class SqlQueryValidatorOutputColumnAliasQueriesDataProvider
{
    /**
     * @return iterable<string, array{sql: string}>
     */
    public static function provideValidQueries(): iterable
    {
        yield 'order by alias of renamed exposed column is allowed' => [
            'sql' => 'SELECT username AS login FROM administrators ORDER BY login LIMIT 10',
        ];

        yield 'order by alias with direction and nulls ordering is allowed' => [
            'sql' => 'SELECT username AS login FROM administrators ORDER BY login DESC NULLS LAST, id LIMIT 10',
        ];

        yield 'bare order by alias named as hidden column sorts by the output column and is allowed' => [
            'sql' => 'SELECT username AS password FROM administrators ORDER BY password LIMIT 10',
        ];

        yield 'order by alias inside derived table is allowed' => [
            'sql' => 'SELECT x.login FROM (SELECT username AS login FROM administrators ORDER BY login) x LIMIT 10',
        ];

        yield 'order by input column inside expression is allowed' => [
            'sql' => 'SELECT username AS login FROM administrators ORDER BY lower(username) LIMIT 10',
        ];
    }

    /**
     * @return iterable<string, array{sql: string, expectedErrorMessage: string}>
     */
    public static function provideInvalidQueries(): iterable
    {
        yield 'alias of hidden column name inside order by function is rejected' => [
            'sql' => 'SELECT username AS password FROM administrators ORDER BY lower(password) LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'alias of hidden column name inside order by comparison is rejected' => [
            'sql' => "SELECT username AS password FROM administrators ORDER BY (password < '\$2y\$12\$M')::int DESC, username LIMIT 50",
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'alias of hidden column name inside order by cast is rejected' => [
            'sql' => 'SELECT username AS password FROM administrators ORDER BY password::text LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'alias of hidden column name inside order by case expression is rejected' => [
            'sql' => "SELECT username AS password FROM administrators ORDER BY CASE WHEN password > 'x' THEN 1 ELSE 0 END LIMIT 10",
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'alias of hidden column name in group by is rejected' => [
            'sql' => 'SELECT COUNT(id) AS password FROM administrators GROUP BY password LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_GROUP_BY_OUTPUT_COLUMN_NOT_SUPPORTED,
        ];

        yield 'alias of hidden column name inside group by expression is rejected' => [
            'sql' => 'SELECT COUNT(id) AS password FROM administrators GROUP BY lower(password) LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'output alias in group by is not supported even without collision with input column' => [
            'sql' => 'SELECT lower(username) AS login, COUNT(id) FROM administrators GROUP BY login LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_GROUP_BY_OUTPUT_COLUMN_NOT_SUPPORTED,
        ];

        yield 'hidden column in order by is rejected when output column names cannot be determined' => [
            'sql' => 'SELECT id + 1 FROM administrators ORDER BY password LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'alias of other hidden column name inside order by function is rejected' => [
            'sql' => 'SELECT username AS google_authenticator_secret FROM administrators ORDER BY lower(google_authenticator_secret) LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];
    }
}

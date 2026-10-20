<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Database\Query;

use Shopsys\McpBundle\Component\Database\Query\SqlQueryValidator;

class SqlQueryValidatorSetOperationQueriesDataProvider
{
    /**
     * @return iterable<string, array{sql: string}>
     */
    public static function provideValidQueries(): iterable
    {
        yield 'union of exposed tables is allowed' => [
            'sql' => 'SELECT id FROM products UNION SELECT id FROM orders LIMIT 10',
        ];

        yield 'intersect of exposed qualified columns is allowed' => [
            'sql' => 'SELECT p.id FROM products p INTERSECT SELECT o.id FROM orders o LIMIT 10',
        ];

        yield 'except of exposed tables is allowed' => [
            'sql' => 'SELECT id FROM products EXCEPT SELECT translatable_id FROM product_translations LIMIT 10',
        ];

        yield 'union of three branches is allowed' => [
            'sql' => 'SELECT id FROM products UNION SELECT id FROM orders UNION ALL SELECT id FROM administrators LIMIT 10',
        ];

        yield 'union ordered by output column of left branch is allowed' => [
            'sql' => 'SELECT id, catnum FROM products UNION ALL SELECT id, currency_code FROM orders ORDER BY catnum LIMIT 10',
        ];

        yield 'parenthesized union branches with own order by and limit are allowed' => [
            'sql' => '(SELECT id FROM products ORDER BY id LIMIT 5) UNION ALL (SELECT id FROM orders ORDER BY id LIMIT 5) LIMIT 10',
        ];

        yield 'union branch can reference cte' => [
            'sql' => 'WITH product_ids AS (SELECT id FROM products) SELECT id FROM product_ids UNION SELECT id FROM orders LIMIT 10',
        ];

        yield 'union in cte body is allowed' => [
            'sql' => 'WITH ids AS (SELECT id FROM products UNION SELECT id FROM orders) SELECT id FROM ids LIMIT 10',
        ];

        yield 'union in derived table is allowed' => [
            'sql' => 'SELECT x.id FROM (SELECT id FROM products UNION SELECT id FROM orders) x LIMIT 10',
        ];

        yield 'correlated union subquery is allowed' => [
            'sql' => 'SELECT p.id FROM products p WHERE p.id IN (SELECT pt.translatable_id FROM product_translations pt WHERE pt.translatable_id = p.id UNION SELECT o.id FROM orders o) LIMIT 10',
        ];
    }

    /**
     * @return iterable<string, array{sql: string, expectedErrorMessage: string}>
     */
    public static function provideInvalidQueries(): iterable
    {
        yield 'hidden column in union branch is rejected' => [
            'sql' => 'SELECT username FROM administrators UNION ALL SELECT password FROM administrators LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        $forbiddenSetOperationBranches = [
            'non exposed table' => [
                'sql' => 'SELECT count(*) FROM administrator_mcp_tokens',
                'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
            ],
            'non public schema table' => [
                'sql' => 'SELECT count(*) FROM pg_catalog.pg_class',
                'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
            ],
            'hidden column in derived table' => [
                'sql' => 'SELECT count(*) FROM (SELECT password FROM administrators) s',
                'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
            ],
            'non exposed table in derived table' => [
                'sql' => 'SELECT count(*) FROM (SELECT 1 FROM administrator_mcp_tokens) s',
                'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
            ],
        ];

        foreach (['UNION', 'UNION ALL', 'INTERSECT', 'EXCEPT'] as $setOperator) {
            foreach ($forbiddenSetOperationBranches as $branchDescription => $forbiddenBranch) {
                yield sprintf('%s in right %s branch is rejected', $branchDescription, $setOperator) => [
                    'sql' => sprintf('SELECT 0 %s %s LIMIT 10', $setOperator, $forbiddenBranch['sql']),
                    'expectedErrorMessage' => $forbiddenBranch['expectedErrorMessage'],
                ];

                yield sprintf('%s in left %s branch is rejected', $branchDescription, $setOperator) => [
                    'sql' => sprintf('%s %s SELECT 0 LIMIT 10', $forbiddenBranch['sql'], $setOperator),
                    'expectedErrorMessage' => $forbiddenBranch['expectedErrorMessage'],
                ];
            }
        }

        yield 'oracle subquery in union branch is rejected' => [
            'sql' => "SELECT 0 UNION ALL SELECT count(*) FROM (SELECT 1 FROM generate_series(1, (SELECT ascii(substr(password,1,1)) FROM administrators WHERE username = 'superadmin'))) s LIMIT 10",
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT,
        ];

        yield 'non exposed table in third union branch is rejected' => [
            'sql' => 'SELECT id FROM products UNION SELECT id FROM orders UNION SELECT id FROM administrator_mcp_tokens LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
        ];

        yield 'non exposed table in union inside cte body is rejected' => [
            'sql' => 'WITH ids AS (SELECT id FROM products UNION SELECT id FROM administrator_mcp_tokens) SELECT id FROM ids LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
        ];

        yield 'non exposed table in union inside where subquery is rejected' => [
            'sql' => 'SELECT p.id FROM products p WHERE p.id IN (SELECT o.id FROM orders o UNION SELECT t.id FROM administrator_mcp_tokens t) LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_TABLE_NOT_EXPOSED,
        ];

        yield 'unreferenced cte does not expose hidden column in union branch' => [
            'sql' => 'WITH c AS (SELECT 1 AS password) SELECT 1 UNION ALL SELECT password FROM administrators LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'hidden column in union order by is rejected' => [
            'sql' => 'SELECT id FROM administrators UNION SELECT id FROM administrators ORDER BY password LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_COLUMN_NOT_EXPOSED,
        ];

        yield 'union without top level limit is rejected' => [
            'sql' => 'SELECT id FROM products UNION (SELECT id FROM orders LIMIT 10)',
            'expectedErrorMessage' => sprintf(SqlQueryValidator::ERROR_LIMIT_REQUIRED_FORMAT, SqlQueryValidatorTest::MAX_RETURNED_ROWS),
        ];
    }
}

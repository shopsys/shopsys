<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Database\Query;

use Shopsys\McpBundle\Component\Database\Query\SqlQueryValidator;

class SqlQueryValidatorNodeQueriesDataProvider
{
    /**
     * @return iterable<string, array{sql: string}>
     */
    public static function provideValidQueries(): iterable
    {
        yield 'boolean conditions are allowed' => [
            'sql' => 'SELECT id FROM products WHERE id > 1 AND (catnum IS NOT NULL OR id < 5) AND NOT id = 3 LIMIT 10',
        ];

        yield 'case expression is allowed' => [
            'sql' => 'SELECT CASE WHEN id > 10 THEN \'big\' WHEN id > 5 THEN \'medium\' ELSE \'small\' END AS size FROM products LIMIT 10',
        ];

        yield 'simple case expression is allowed' => [
            'sql' => 'SELECT CASE code WHEN \'CZK\' THEN 1 ELSE 0 END FROM currencies LIMIT 10',
        ];

        yield 'between in like and ilike are allowed' => [
            'sql' => 'SELECT id FROM products WHERE id BETWEEN 1 AND 10 AND id IN (1, 2, 3) AND catnum LIKE \'A%\' AND catnum ILIKE \'%b\' LIMIT 10',
        ];

        yield 'is distinct from and nullif are allowed' => [
            'sql' => 'SELECT NULLIF(catnum, \'\') FROM products WHERE catnum IS DISTINCT FROM \'x\' LIMIT 10',
        ];

        yield 'boolean test is allowed' => [
            'sql' => 'SELECT id FROM products WHERE (id > 1) IS TRUE LIMIT 10',
        ];

        yield 'greatest and least are allowed' => [
            'sql' => 'SELECT GREATEST(id, 5), LEAST(id, 1) FROM products LIMIT 10',
        ];

        yield 'array with any is allowed' => [
            'sql' => 'SELECT id FROM products WHERE id = ANY (ARRAY[1, 2, 3]) LIMIT 10',
        ];

        yield 'array subscript is allowed' => [
            'sql' => 'SELECT (ARRAY[1, 2, 3])[1] LIMIT 1',
        ];

        yield 'all columns of row are allowed' => [
            'sql' => 'SELECT (p).* FROM products p LIMIT 1',
        ];

        yield 'row comparison with subquery is allowed' => [
            'sql' => 'SELECT id FROM products WHERE (id, catnum) IN (SELECT id, catnum FROM products) LIMIT 10',
        ];

        yield 'left join with null test is allowed' => [
            'sql' => 'SELECT p.id FROM products p LEFT JOIN product_translations pt ON pt.translatable_id = p.id WHERE pt.id IS NULL LIMIT 10',
        ];

        yield 'group by with having is allowed' => [
            'sql' => 'SELECT customer_user_id, count(*) FROM orders GROUP BY customer_user_id HAVING count(*) > 1 LIMIT 10',
        ];

        yield 'grouping sets are allowed' => [
            'sql' => 'SELECT domain_id, GROUPING(domain_id), count(*) FROM orders GROUP BY ROLLUP (domain_id) LIMIT 10',
        ];

        yield 'window clause is allowed' => [
            'sql' => 'SELECT id, sum(id) OVER w FROM products WINDOW w AS (PARTITION BY catnum ORDER BY id) LIMIT 10',
        ];

        yield 'aggregate filter is allowed' => [
            'sql' => 'SELECT count(*) FILTER (WHERE id > 1) FROM products LIMIT 1',
        ];

        yield 'distinct on is allowed' => [
            'sql' => 'SELECT DISTINCT ON (catnum) id, catnum FROM products ORDER BY catnum, id LIMIT 10',
        ];

        yield 'values list is allowed' => [
            'sql' => 'SELECT v.x FROM (VALUES (1), (2)) AS v(x) LIMIT 10',
        ];

        yield 'interval arithmetic is allowed' => [
            'sql' => 'SELECT id FROM orders WHERE created_at > current_date - INTERVAL \'7 days\' LIMIT 10',
        ];

        yield 'jsonb operators are allowed' => [
            'sql' => 'SELECT \'{"a": 1}\'::jsonb ->> \'a\' LIMIT 1',
        ];

        yield 'unqualified collation is allowed' => [
            'sql' => 'SELECT catnum FROM products ORDER BY catnum COLLATE "C" LIMIT 10',
        ];

        yield 'offset is allowed' => [
            'sql' => 'SELECT id FROM products ORDER BY id LIMIT 10 OFFSET 20',
        ];
    }

    /**
     * @return iterable<string, array{sql: string, expectedErrorMessage: string}>
     */
    public static function provideInvalidQueries(): iterable
    {
        yield 'function called through field selection is rejected' => [
            'sql' => 'SELECT (\'server_version\'::text).current_setting LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_CONSTRUCT,
        ];

        yield 'function called through field selection of derived table column is rejected' => [
            'sql' => 'SELECT (x.q).ts_stat FROM (SELECT \'SELECT to_tsvector(username) FROM administrators\' AS q) x LIMIT 5',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_CONSTRUCT,
        ];

        yield 'field selection after subscript is rejected' => [
            'sql' => 'SELECT (ARRAY[\'pg_authid\'])[1].to_regclass LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_CONSTRUCT,
        ];

        yield 'sql json function is rejected' => [
            'sql' => 'SELECT JSON_VALUE(\'1\', \'$\' RETURNING int4) LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_CONSTRUCT,
        ];

        yield 'is json predicate is rejected' => [
            'sql' => 'SELECT \'{}\' IS JSON LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_CONSTRUCT,
        ];

        yield 'xml expression is rejected' => [
            'sql' => 'SELECT XMLELEMENT(NAME a) LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_CONSTRUCT,
        ];

        yield 'tablesample is rejected' => [
            'sql' => 'SELECT id FROM products TABLESAMPLE SYSTEM (10) LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_CONSTRUCT,
        ];

        yield 'parameter reference is rejected' => [
            'sql' => 'SELECT $1 LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_UNSUPPORTED_CONSTRUCT,
        ];
    }
}

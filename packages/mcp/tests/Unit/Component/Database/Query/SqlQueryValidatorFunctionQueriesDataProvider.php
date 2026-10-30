<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Database\Query;

use Shopsys\McpBundle\Component\Database\Query\SqlQueryValidator;

class SqlQueryValidatorFunctionQueriesDataProvider
{
    /**
     * @return iterable<string, array{sql: string}>
     */
    public static function provideValidQueries(): iterable
    {
        yield 'window functions are allowed' => [
            'sql' => 'SELECT id, row_number() OVER (ORDER BY id), rank() OVER (PARTITION BY domain_id ORDER BY id), lag(id) OVER (ORDER BY id) FROM orders LIMIT 10',
        ];

        yield 'ordered set aggregate is allowed' => [
            'sql' => 'SELECT percentile_cont(0.5) WITHIN GROUP (ORDER BY id) FROM orders LIMIT 1',
        ];

        yield 'array and boolean aggregates are allowed' => [
            'sql' => 'SELECT domain_id, array_agg(id ORDER BY id), bool_or(id > 10) FROM orders GROUP BY domain_id LIMIT 10',
        ];

        yield 'date functions are allowed' => [
            'sql' => 'SELECT date_part(\'year\', created_at), age(now(), created_at), date_bin(\'1 day\', created_at, \'2000-01-01\') FROM orders LIMIT 10',
        ];

        yield 'sql standard syntax of allowed functions is allowed' => [
            'sql' => 'SELECT EXTRACT(YEAR FROM created_at), substring(number FROM 1 FOR 2), position(\'1\' IN number), trim(BOTH \' \' FROM number), created_at AT TIME ZONE \'UTC\' FROM orders LIMIT 10',
        ];

        yield 'overlaps is allowed' => [
            'sql' => 'SELECT id FROM orders WHERE (created_at, created_at) OVERLAPS (current_date - 7, current_date) LIMIT 10',
        ];

        yield 'statistical functions are allowed' => [
            'sql' => 'SELECT stddev_samp(id), var_pop(id), corr(id, domain_id), max(width_bucket(id, 0, 1000, 10)) FROM orders LIMIT 1',
        ];

        yield 'date function is allowed' => [
            'sql' => 'SELECT date(created_at), count(*) FROM orders GROUP BY date(created_at) LIMIT 10',
        ];

        yield 'string functions are allowed' => [
            'sql' => 'SELECT left(catnum, 3), initcap(catnum), concat_ws(\'-\', catnum, id), regexp_replace(catnum, \'[0-9]\', \'\', \'g\') FROM products LIMIT 10',
        ];

        yield 'json functions are allowed' => [
            'sql' => 'SELECT jsonb_build_object(\'id\', id), jsonb_typeof(\'[]\'::jsonb) FROM products LIMIT 10',
        ];
    }

    /**
     * @return iterable<string, array{sql: string, expectedErrorMessage: string}>
     */
    public static function provideInvalidQueries(): iterable
    {
        yield 'allowed function called with schema is rejected' => [
            'sql' => 'SELECT pg_catalog.extract(\'year\', created_at) FROM orders LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_FUNCTION_NOT_ALLOWED,
        ];

        yield 'sql standard syntax of not allowed function is rejected' => [
            'sql' => 'SELECT collation for (number) FROM orders LIMIT 10',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_FUNCTION_NOT_ALLOWED,
        ];

        yield 'padding function allocating memory beyond the statement timeout is rejected' => [
            'sql' => 'SELECT lpad(\'\', 200000000, \'x\') LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_FUNCTION_NOT_ALLOWED,
        ];

        yield 'format with width is rejected' => [
            'sql' => 'SELECT format(\'%200000000s\', \'x\') LIMIT 1',
            'expectedErrorMessage' => SqlQueryValidator::ERROR_FUNCTION_NOT_ALLOWED,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Query;

class ParsedSqlQuery
{
    /**
     * @param array<string, mixed> $statement
     */
    public function __construct(
        public readonly string $singleStatementSql,
        public readonly array $statement,
    ) {
    }
}

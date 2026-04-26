<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Query;

class SqlQueryValidationResult
{
    protected function __construct(
        public readonly bool $isValid,
        public readonly ?string $singleStatementSql,
        public readonly ?string $errorMessage,
    ) {
    }

    public static function createValid(string $singleStatementSql): self
    {
        return new self(true, $singleStatementSql, null);
    }

    public static function createInvalid(string $errorMessage): self
    {
        return new self(false, null, $errorMessage);
    }
}

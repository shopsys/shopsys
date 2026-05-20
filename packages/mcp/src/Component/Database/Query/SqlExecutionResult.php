<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Query;

class SqlExecutionResult
{
    /**
     * @param array{
     *     columnNames: array<string>,
     *     rows: array<int, array<string, string|int|float|bool|null>>,
     *     rowCount: int,
     *     durationMs: float
     * }|null $data
     */
    protected function __construct(
        public readonly bool $isValid,
        public readonly ?array $data,
        public readonly ?string $errorMessage,
    ) {
    }

    /**
     * @param array{
     *     columnNames: array<string>,
     *     rows: array<int, array<string, string|int|float|bool|null>>,
     *     rowCount: int,
     *     durationMs: float
     * } $data
     */
    public static function createValid(array $data): self
    {
        return new self(true, $data, null);
    }

    public static function createInvalid(string $errorMessage): self
    {
        return new self(false, null, $errorMessage);
    }
}

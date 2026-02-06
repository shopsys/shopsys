<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker;

final class WorkerResult
{
    /**
     * @param array<string> $filesModified
     * @param array<string> $filesCreated
     * @param array<string> $filesDeleted
     * @param array<string> $hints
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly array $filesModified = [],
        public readonly array $filesCreated = [],
        public readonly array $filesDeleted = [],
        public readonly array $hints = [],
    ) {
    }

    /**
     * @param array<string> $filesModified
     * @param array<string> $filesCreated
     * @param array<string> $filesDeleted
     * @param array<string> $hints
     */
    public static function success(
        string $message,
        array $filesModified = [],
        array $filesCreated = [],
        array $filesDeleted = [],
        array $hints = [],
    ): self {
        return new self(
            success: true,
            message: $message,
            filesModified: $filesModified,
            filesCreated: $filesCreated,
            filesDeleted: $filesDeleted,
            hints: $hints,
        );
    }

    public static function failure(string $message): self
    {
        return new self(
            success: false,
            message: $message,
        );
    }
}

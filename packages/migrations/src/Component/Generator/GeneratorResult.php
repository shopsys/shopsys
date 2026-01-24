<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Component\Generator;

class GeneratorResult
{
    public function __construct(protected string $migrationFilePath, protected int|false $writtenBytes)
    {
    }

    public function getMigrationFilePath(): string
    {
        return $this->migrationFilePath;
    }

    public function getWrittenBytes(): false|int
    {
        return $this->writtenBytes;
    }

    public function hasError(): bool
    {
        return $this->writtenBytes === false || $this->writtenBytes === 0;
    }
}

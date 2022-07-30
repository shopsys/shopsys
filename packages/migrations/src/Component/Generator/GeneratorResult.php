<?php

namespace Shopsys\MigrationBundle\Component\Generator;

class GeneratorResult
{
    /**
     * @var string
     */
    protected $migrationFilePath;

    /**
     * @var int|false
     */
    protected $writtenBytes;

    /**
     * @param string $migrationFilePath
     * @param int|false $writtenBytes
     */
    public function __construct(string $migrationFilePath, $writtenBytes)
    {
        $this->migrationFilePath = $migrationFilePath;
        $this->writtenBytes = $writtenBytes;
    }

    /**
     * @return string
     */
    public function getMigrationFilePath(): string
    {
        return $this->migrationFilePath;
    }

    /**
     * @return false|int
     */
    public function getWrittenBytes(): int|false
    {
        return $this->writtenBytes;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->writtenBytes === false || $this->writtenBytes === 0;
    }
}

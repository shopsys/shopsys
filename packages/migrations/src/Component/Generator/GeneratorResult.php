<?php

namespace Shopsys\MigrationBundle\Component\Generator;

class GeneratorResult
{

    /**
     * @var string
     */
    private $migrationFilePath;

    /**
     * @var int|false
     */
    private $writtenBytes;

    /**
     * @param int|false $writtenBytes
     */
    public function __construct(string $migrationFilePath, $writtenBytes)
    {
        $this->migrationFilePath = $migrationFilePath;
        $this->writtenBytes = $writtenBytes;
    }

    public function getMigrationFilePath(): string
    {
        return $this->migrationFilePath;
    }

    /**
     * @return false|int
     */
    public function getWrittenBytes()
    {
        return $this->writtenBytes;
    }

    public function hasError(): bool
    {
        return $this->writtenBytes === false || $this->writtenBytes === 0;
    }
}

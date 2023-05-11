<?php

namespace Shopsys\MigrationBundle\Component\Generator;

class GeneratorResult
{
    protected string $migrationFilePath;

    protected int|false $writtenBytes;

    /**
     * @param string $migrationFilePath
     * @param int|false $writtenBytes
     */
    public function __construct($migrationFilePath, $writtenBytes)
    {
        $this->migrationFilePath = $migrationFilePath;
        $this->writtenBytes = $writtenBytes;
    }

    /**
     * @return string
     */
    public function getMigrationFilePath()
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

    /**
     * @return bool
     */
    public function hasError()
    {
        return $this->writtenBytes === false || $this->writtenBytes === 0;
    }
}

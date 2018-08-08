<?php

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

class MigrationsLocation
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @param string $directory
     * @param string $namespace
     */
    public function __construct($directory, $namespace)
    {
        $this->directory = $directory;
        $this->namespace = $namespace;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }
}

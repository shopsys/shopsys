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
    
    public function __construct(string $directory, string $namespace)
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

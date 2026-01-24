<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

class MigrationsLocation
{
    public function __construct(protected string $directory, protected string $namespace)
    {
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

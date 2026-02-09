<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

class DatabaseConnectionCredentialsProvider
{
    public function __construct(
        protected readonly string $databaseHost,
        protected readonly string $databasePort,
        protected readonly string $databaseName,
        protected readonly string $databaseUsername,
        protected readonly string $databasePassword,
    ) {
    }

    public function getDatabaseHost(): string
    {
        return $this->databaseHost;
    }

    public function getDatabasePort(): string
    {
        return $this->databasePort;
    }

    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    public function getDatabaseUsername(): string
    {
        return $this->databaseUsername;
    }

    public function getDatabasePassword(): string
    {
        return $this->databasePassword;
    }
}

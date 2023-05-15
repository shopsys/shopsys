<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

class DatabaseConnectionCredentialsProvider
{
    /**
     * @param string $databaseHost
     * @param string $databasePort
     * @param string $databaseName
     * @param string $databaseUsername
     * @param string $databasePassword
     */
    public function __construct(
        protected readonly string $databaseHost,
        protected readonly string $databasePort,
        protected readonly string $databaseName,
        protected readonly string $databaseUsername,
        protected readonly string $databasePassword,
    ) {
    }

    /**
     * @return string
     */
    public function getDatabaseHost(): string
    {
        return $this->databaseHost;
    }

    /**
     * @return string
     */
    public function getDatabasePort(): string
    {
        return $this->databasePort;
    }

    /**
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    /**
     * @return string
     */
    public function getDatabaseUsername(): string
    {
        return $this->databaseUsername;
    }

    /**
     * @return string
     */
    public function getDatabasePassword(): string
    {
        return $this->databasePassword;
    }

    /**
     * @return string
     */
    public function getConnectionDsn(): string
    {
        $dsnParams = [
            'host' => $this->getDatabaseHost(),
            'port' => $this->getDatabasePort(),
            'dbname' => $this->getDatabaseName(),
        ];

        return 'pgsql:' . http_build_query($dsnParams, '', ';');
    }
}

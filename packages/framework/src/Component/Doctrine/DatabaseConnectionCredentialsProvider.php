<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

class DatabaseConnectionCredentialsProvider
{
    /**
     * @var string
     */
    protected string $databaseHost;

    /**
     * @var string
     */
    protected string $databasePort;

    /**
     * @var string
     */
    protected string $databaseName;

    /**
     * @var string
     */
    protected string $databaseUsername;

    /**
     * @var string
     */
    protected string $databasePassword;

    /**
     * @param string $databaseHost
     * @param string $databasePort
     * @param string $databaseName
     * @param string $databaseUsername
     * @param string $databasePassword
     */
    public function __construct(
        string $databaseHost,
        string $databasePort,
        string $databaseName,
        string $databaseUsername,
        string $databasePassword
    ) {
        $this->databaseHost = $databaseHost;
        $this->databasePort = $databasePort;
        $this->databaseName = $databaseName;
        $this->databaseUsername = $databaseUsername;
        $this->databasePassword = $databasePassword;
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

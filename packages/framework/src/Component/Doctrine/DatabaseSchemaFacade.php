<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\DefaultSchemaImportException;

class DatabaseSchemaFacade
{
    public function __construct(
        protected string $defaultSchemaFilepath,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function createSchema(string $schemaName): void
    {
        $connection = $this->em->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        $connection->executeStatement('CREATE SCHEMA ' . $databasePlatform->quoteSingleIdentifier($schemaName));
    }

    public function dropSchemaIfExists(string $schemaName): void
    {
        $connection = $this->em->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        $connection->executeStatement(
            'DROP SCHEMA IF EXISTS ' . $databasePlatform->quoteSingleIdentifier($schemaName) . ' CASCADE',
        );
    }

    public function importDefaultSchema(): void
    {
        $connection = $this->em->getConnection();
        $handle = fopen($this->defaultSchemaFilepath, 'r');

        if (!$handle) {
            $message = 'Failed to open file ' . $this->defaultSchemaFilepath . ' with default database schema.';

            throw new DefaultSchemaImportException($message);
        }

        $line = fgets($handle);

        while ($line !== false) {
            $connection->executeStatement($line);
            $line = fgets($handle);
        }
        fclose($handle);
    }

    public function dropTables(): void
    {
        $connection = $this->em->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        $tableNames = $connection->fetchAllAssociative('SELECT tablename FROM pg_tables WHERE schemaname = \'public\'');

        foreach ($tableNames as $tableName) {
            $connection->executeStatement(
                'DROP TABLE IF EXISTS ' . $databasePlatform->quoteSingleIdentifier($tableName['tablename']) . ' CASCADE',
            );
        }
    }
}

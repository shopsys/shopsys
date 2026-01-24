<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\DefaultSchemaImportException;

class DatabaseSchemaFacade
{
    protected string $defaultSchemaFilepath;

    public function __construct(
        mixed $defaultSchemaFilepath,
        protected readonly EntityManagerInterface $em,
    ) {
        $this->defaultSchemaFilepath = $defaultSchemaFilepath;
    }

    public function createSchema(string $schemaName): void
    {
        $this->em->getConnection()->executeQuery('CREATE SCHEMA ' . $schemaName);
    }

    public function dropSchemaIfExists(string $schemaName): void
    {
        $this->em->getConnection()->executeQuery('DROP SCHEMA IF EXISTS ' . $schemaName . ' CASCADE');
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
            $connection->executeQuery($line);
            $line = fgets($handle);
        }
        fclose($handle);
    }

    public function dropTables(): void
    {
        $connection = $this->em->getConnection();
        $tableNames = $connection->fetchAllAssociative('SELECT tablename FROM pg_tables WHERE schemaname = \'public\'');

        foreach ($tableNames as $tableName) {
            $connection->executeQuery('DROP TABLE IF EXISTS ' . $tableName['tablename'] . ' CASCADE');
        }
    }
}

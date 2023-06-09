<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\DefaultSchemaImportException;

class DatabaseSchemaFacade
{
    protected string $defaultSchemaFilepath;

    /**
     * @param mixed $defaultSchemaFilepath
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        $defaultSchemaFilepath,
        protected readonly EntityManagerInterface $em,
    ) {
        $this->defaultSchemaFilepath = $defaultSchemaFilepath;
    }

    /**
     * @param string $schemaName
     */
    public function createSchema($schemaName)
    {
        $this->em->getConnection()->executeQuery('CREATE SCHEMA ' . $schemaName);
    }

    /**
     * @param string $schemaName
     */
    public function dropSchemaIfExists($schemaName)
    {
        $this->em->getConnection()->executeQuery('DROP SCHEMA IF EXISTS ' . $schemaName . ' CASCADE');
    }

    public function importDefaultSchema()
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
}

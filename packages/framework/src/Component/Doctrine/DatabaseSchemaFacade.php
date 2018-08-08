<?php

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

class DatabaseSchemaFacade
{
    /**
     * @var string
     */
    protected $defaultSchemaFilepath;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(
        $defaultSchemaFilepath,
        EntityManagerInterface $em
    ) {
        $this->defaultSchemaFilepath = $defaultSchemaFilepath;
        $this->em = $em;
    }

    public function createSchema(string $schemaName): void
    {
        $this->em->getConnection()->query('CREATE SCHEMA ' . $schemaName);
    }

    public function dropSchemaIfExists(string $schemaName): void
    {
        $this->em->getConnection()->query('DROP SCHEMA IF EXISTS ' . $schemaName . ' CASCADE');
    }

    public function importDefaultSchema(): void
    {
        $connection = $this->em->getConnection();
        $handle = fopen($this->defaultSchemaFilepath, 'r');
        if ($handle) {
            $line = fgets($handle);
            while ($line !== false) {
                $connection->query($line);
                $line = fgets($handle);
            }
            fclose($handle);
        } else {
            $message = 'Failed to open file ' . $this->defaultSchemaFilepath . ' with default database schema.';
            throw new \Shopsys\FrameworkBundle\Component\Doctrine\Exception\DefaultSchemaImportException($message);
        }
    }
}

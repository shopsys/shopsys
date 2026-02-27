<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Component\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class DatabaseSchemaFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly SchemaDiffFilter $schemaDiffFilter,
        protected readonly SchemaTool $schemaTool,
    ) {
    }

    /**
     * @return string[]
     */
    public function getFilteredSchemaDiffSqlCommands(): array
    {
        $allMetadata = $this->em->getMetadataFactory()->getAllMetadata();

        $schemaManager = $this->em->getConnection()->createSchemaManager();
        $databaseSchema = $schemaManager->introspectSchema();
        $metadataSchema = $this->schemaTool->getSchemaFromMetadata($allMetadata);

        $schemaDiff = $schemaManager->createComparator()->compareSchemas($databaseSchema, $metadataSchema);
        $filteredSchemaDiff = $this->schemaDiffFilter->getFilteredSchemaDiff($schemaDiff);

        return $this->em->getConnection()->getDatabasePlatform()->getAlterSchemaSQL($filteredSchemaDiff);
    }
}

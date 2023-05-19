<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Component\Doctrine;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class DatabaseSchemaFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\MigrationBundle\Component\Doctrine\SchemaDiffFilter $schemaDiffFilter
     * @param \Doctrine\DBAL\Schema\Comparator $comparator
     * @param \Doctrine\ORM\Tools\SchemaTool $schemaTool
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly SchemaDiffFilter $schemaDiffFilter,
        protected readonly Comparator $comparator,
        protected readonly SchemaTool $schemaTool,
    ) {
    }

    /**
     * @return string[]
     */
    public function getFilteredSchemaDiffSqlCommands()
    {
        $allMetadata = $this->em->getMetadataFactory()->getAllMetadata();

        $databaseSchema = $this->em->getConnection()->createSchemaManager()->createSchema();
        $metadataSchema = $this->schemaTool->getSchemaFromMetadata($allMetadata);

        $schemaDiff = $this->comparator->compareSchemas($databaseSchema, $metadataSchema);
        $filteredSchemaDiff = $this->schemaDiffFilter->getFilteredSchemaDiff($schemaDiff);

        return $filteredSchemaDiff->toSaveSql($this->em->getConnection()->getDatabasePlatform());
    }
}

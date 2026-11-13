<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Schema;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

class AllowedDatabaseTablesProvider
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly SchemaNameNormalizer $schemaNameNormalizer,
    ) {
    }

    /**
     * @return array<string, \Doctrine\ORM\Mapping\ClassMetadata>
     */
    public function getAllAllowedClassMetadataIndexedByTableNames(): array
    {
        $allowedClassMetadataIndexedByTableNames = [];

        foreach ($this->entityManager->getMetadataFactory()->getAllMetadata() as $classMetadata) {
            if (!$classMetadata instanceof ClassMetadata) {
                continue;
            }

            if (!$this->isTableExposed($classMetadata)) {
                continue;
            }

            $tableName = $this->schemaNameNormalizer->normalizeTableNameFromString($classMetadata->getTableName());
            $allowedClassMetadataIndexedByTableNames[$tableName] = $classMetadata;
        }

        ksort($allowedClassMetadataIndexedByTableNames);

        return $allowedClassMetadataIndexedByTableNames;
    }

    protected function isTableExposed(ClassMetadata $classMetadata): bool
    {
        $asMcpTableAttributes = $classMetadata->getReflectionClass()->getAttributes(AsMcpTable::class);

        return $asMcpTableAttributes !== [] && $asMcpTableAttributes[0]->newInstance()->exposed;
    }
}

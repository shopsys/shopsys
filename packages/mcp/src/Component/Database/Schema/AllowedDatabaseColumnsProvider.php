<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Schema;

use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\JoinColumnMapping;
use LogicException;
use ReflectionProperty;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpInheritedColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

class AllowedDatabaseColumnsProvider
{
    public function __construct(
        protected readonly AllowedDatabaseTablesProvider $allowedDatabaseTablesProvider,
        protected readonly SchemaNameNormalizer $schemaNameNormalizer,
    ) {
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public function getAllAllowedColumnsSetIndexedByTableNames(): array
    {
        $allowedColumnsSetIndexedByTableNames = [];

        foreach ($this->allowedDatabaseTablesProvider->getAllAllowedClassMetadataIndexedByTableNames() as $tableName => $classMetadata) {
            $allowedColumnsSetIndexedByTableNames[$tableName] = $this->getAllowedColumnsSetIndexedByColumnNames(
                $classMetadata,
                $classMetadata->getReflectionClass()->getProperties(),
            );

            foreach ($this->getAllowedJoinTableColumnsSetIndexedByTableNames($classMetadata) as $joinTableName => $columnsSet) {
                $allowedColumnsSetIndexedByTableNames[$joinTableName] = $columnsSet;
            }
        }

        ksort($allowedColumnsSetIndexedByTableNames);

        return $allowedColumnsSetIndexedByTableNames;
    }

    /**
     * @param array<int, \ReflectionProperty> $properties
     * @return array<string, bool>
     */
    protected function getAllowedColumnsSetIndexedByColumnNames(ClassMetadata $classMetadata, array $properties): array
    {
        $allowedColumnsSetIndexedByColumnNames = [];
        $classLevelColumnExposureByFieldNames = $this->getClassLevelColumnExposureByFieldNames($classMetadata);

        foreach ($properties as $property) {
            if (!$this->isColumnExposed($property, $classLevelColumnExposureByFieldNames)) {
                continue;
            }

            foreach ($this->getColumnNamesByFieldName($classMetadata, $property->getName()) as $columnName) {
                $allowedColumnsSetIndexedByColumnNames[$columnName] = true;
            }
        }

        ksort($allowedColumnsSetIndexedByColumnNames);

        return $allowedColumnsSetIndexedByColumnNames;
    }

    /**
     * Join table of a many-to-many association has no entity to declare the exposure on,
     * so it is exposed (with both of its join columns) by #[AsMcpTable] on the owning side property of the association
     *
     * @return array<string, array<string, bool>>
     */
    protected function getAllowedJoinTableColumnsSetIndexedByTableNames(ClassMetadata $classMetadata): array
    {
        $allowedColumnsSetIndexedByJoinTableNames = [];

        foreach ($classMetadata->getReflectionClass()->getProperties() as $property) {
            if (!$classMetadata->hasAssociation($property->getName())) {
                continue;
            }

            $associationMapping = $classMetadata->getAssociationMapping($property->getName());

            if (!$associationMapping->isManyToManyOwningSide() || !$this->isJoinTableExposed($property)) {
                continue;
            }

            $joinTable = $associationMapping->joinTable;
            $joinTableName = $this->schemaNameNormalizer->normalizeTableNameFromString($joinTable->name);

            foreach ([...$joinTable->joinColumns, ...$joinTable->inverseJoinColumns] as $joinColumn) {
                $allowedColumnsSetIndexedByJoinTableNames[$joinTableName][$this->normalizeColumnName($joinColumn->name)] = true;
            }

            ksort($allowedColumnsSetIndexedByJoinTableNames[$joinTableName]);
        }

        return $allowedColumnsSetIndexedByJoinTableNames;
    }

    protected function isJoinTableExposed(ReflectionProperty $property): bool
    {
        $asMcpTableAttributes = $property->getAttributes(AsMcpTable::class);

        return $asMcpTableAttributes !== [] && $asMcpTableAttributes[0]->newInstance()->exposed;
    }

    /**
     * @return array<string, bool>
     */
    protected function getClassLevelColumnExposureByFieldNames(ClassMetadata $classMetadata): array
    {
        $classLevelColumnExposureByFieldNames = [];

        foreach ($classMetadata->getReflectionClass()->getAttributes(AsMcpInheritedColumn::class) as $attribute) {
            $asMcpInheritedColumn = $attribute->newInstance();

            if (!$classMetadata->hasField($asMcpInheritedColumn->fieldName) && !$classMetadata->hasAssociation($asMcpInheritedColumn->fieldName)) {
                throw new LogicException(sprintf(
                    'Class-level #[AsMcpInheritedColumn(fieldName: "%s")] on "%s" must reference an existing mapped field.',
                    $asMcpInheritedColumn->fieldName,
                    $classMetadata->getName(),
                ));
            }

            if (array_key_exists($asMcpInheritedColumn->fieldName, $classLevelColumnExposureByFieldNames)) {
                throw new LogicException(sprintf(
                    'Class-level #[AsMcpInheritedColumn(fieldName: "%s")] on "%s" must not be declared more than once.',
                    $asMcpInheritedColumn->fieldName,
                    $classMetadata->getName(),
                ));
            }

            $classLevelColumnExposureByFieldNames[$asMcpInheritedColumn->fieldName] = $asMcpInheritedColumn->exposed;
        }

        return $classLevelColumnExposureByFieldNames;
    }

    /**
     * @return array<int, string>
     */
    protected function getColumnNamesByFieldName(ClassMetadata $classMetadata, string $fieldName): array
    {
        if (isset($classMetadata->embeddedClasses[$fieldName])) {
            return $this->getEmbeddedColumnNames($classMetadata, $fieldName);
        }

        if ($classMetadata->hasField($fieldName)) {
            return [$this->normalizeColumnName($classMetadata->getColumnName($fieldName))];
        }

        if (!$classMetadata->hasAssociation($fieldName)) {
            return [];
        }

        $associationMapping = $classMetadata->getAssociationMapping($fieldName);

        if (!$associationMapping->isToOneOwningSide()) {
            return [];
        }

        $columnNames = [];

        foreach ($associationMapping->joinColumns as $joinColumn) {
            if (!$joinColumn instanceof JoinColumnMapping) {
                continue;
            }

            $columnNames[] = $this->normalizeColumnName($joinColumn->name);
        }

        return $columnNames;
    }

    /**
     * Exposing an embedded property exposes the columns of all fields inlined from the embeddable
     *
     * @return array<int, string>
     */
    protected function getEmbeddedColumnNames(ClassMetadata $classMetadata, string $fieldName): array
    {
        $columnNames = [];

        foreach ($classMetadata->getFieldNames() as $embeddedFieldName) {
            if (str_starts_with($embeddedFieldName, $fieldName . '.')) {
                $columnNames[] = $this->normalizeColumnName($classMetadata->getColumnName($embeddedFieldName));
            }
        }

        return $columnNames;
    }

    protected function normalizeColumnName(string $columnName): string
    {
        return $this->schemaNameNormalizer->normalizeColumnName($this->createColumnName($columnName));
    }

    protected function createColumnName(string $columnName): UnqualifiedName
    {
        if ($this->isQuotedIdentifier($columnName)) {
            return UnqualifiedName::quoted($this->getQuotedIdentifierValue($columnName));
        }

        return UnqualifiedName::unquoted($columnName);
    }

    protected function isQuotedIdentifier(string $columnName): bool
    {
        return str_starts_with($columnName, '"') && str_ends_with($columnName, '"');
    }

    protected function getQuotedIdentifierValue(string $columnName): string
    {
        return str_replace('""', '"', substr($columnName, 1, -1));
    }

    /**
     * @param array<string, bool> $classLevelColumnExposureByFieldNames
     */
    protected function isColumnExposed(ReflectionProperty $property, array $classLevelColumnExposureByFieldNames): bool
    {
        $asMcpColumnAttributes = $property->getAttributes(AsMcpColumn::class);

        if ($asMcpColumnAttributes !== []) {
            $asMcpColumn = $asMcpColumnAttributes[0]->newInstance();

            return $asMcpColumn->exposed;
        }

        return $classLevelColumnExposureByFieldNames[$property->getName()] ?? false;
    }
}

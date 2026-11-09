<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class NestedSetValuesRepository
{
    protected const int NESTED_SET_UPDATE_BATCH_SIZE = 1000;

    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param array<int, array{parent: int|null, level: int, lft: int, rgt: int}> $nestedSetValuesIndexedById
     */
    public function updateNestedSetValues(string $entityClassName, array $nestedSetValuesIndexedById): int
    {
        $classMetadata = $this->em->getClassMetadata($entityClassName);
        $nestedSetValuesBatches = array_chunk($nestedSetValuesIndexedById, static::NESTED_SET_UPDATE_BATCH_SIZE, true);

        return $this->em->getConnection()->transactional(
            function (Connection $connection) use ($classMetadata, $nestedSetValuesBatches): int {
                $changedRowsCount = 0;

                foreach ($nestedSetValuesBatches as $nestedSetValuesBatch) {
                    $parameters = [];
                    $rowIndex = 0;

                    foreach ($nestedSetValuesBatch as $id => $nestedSetValues) {
                        $parameters['id' . $rowIndex] = $id;
                        $parameters['parentId' . $rowIndex] = $nestedSetValues['parent'];
                        $parameters['level' . $rowIndex] = $nestedSetValues['level'];
                        $parameters['lft' . $rowIndex] = $nestedSetValues['lft'];
                        $parameters['rgt' . $rowIndex] = $nestedSetValues['rgt'];
                        $rowIndex++;
                    }

                    $changedRowsCount += (int)$connection->executeStatement(
                        $this->getNestedSetValuesUpdateSql($classMetadata, count($nestedSetValuesBatch)),
                        $parameters,
                    );
                }

                return $changedRowsCount;
            },
        );
    }

    protected function getNestedSetValuesUpdateSql(ClassMetadata $classMetadata, int $rowsCount): string
    {
        $valuesRows = [];

        for ($rowIndex = 0; $rowIndex < $rowsCount; $rowIndex++) {
            $valuesRows[] = sprintf(
                '(CAST(:id%1$d AS INTEGER), CAST(:parentId%1$d AS INTEGER), CAST(:level%1$d AS INTEGER), CAST(:lft%1$d AS INTEGER), CAST(:rgt%1$d AS INTEGER))',
                $rowIndex,
            );
        }

        return strtr(
            'UPDATE {table} AS t
            SET {parent} = v.parent_id, {level} = v.level, {lft} = v.lft, {rgt} = v.rgt
            FROM (VALUES {values}) AS v (id, parent_id, level, lft, rgt)
            WHERE t.{id} = v.id
                AND (
                    t.{parent} IS DISTINCT FROM v.parent_id
                    OR t.{level} <> v.level
                    OR t.{lft} <> v.lft
                    OR t.{rgt} <> v.rgt
                )',
            [
                '{table}' => $classMetadata->getTableName(),
                '{id}' => $classMetadata->getSingleIdentifierColumnName(),
                '{parent}' => $classMetadata->getSingleAssociationJoinColumnName('parent'),
                '{level}' => $classMetadata->getColumnName('level'),
                '{lft}' => $classMetadata->getColumnName('lft'),
                '{rgt}' => $classMetadata->getColumnName('rgt'),
                '{values}' => implode(', ', $valuesRows),
            ],
        );
    }
}

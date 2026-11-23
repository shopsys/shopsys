<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

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

            $changedRowsCount += (int)$this->em->getConnection()->executeStatement(
                $this->getNestedSetValuesUpdateSql($classMetadata, count($nestedSetValuesBatch)),
                $parameters,
            );
        }

        return $changedRowsCount;
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

        return sprintf(
            'UPDATE %1$s AS t
            SET %2$s = v.parent_id, %3$s = v.level, %4$s = v.lft, %5$s = v.rgt
            FROM (VALUES %7$s) AS v (id, parent_id, level, lft, rgt)
            WHERE t.%6$s = v.id
                AND (
                    t.%2$s IS DISTINCT FROM v.parent_id
                    OR t.%3$s <> v.level
                    OR t.%4$s <> v.lft
                    OR t.%5$s <> v.rgt
                )',
            $classMetadata->getTableName(),
            $classMetadata->getSingleAssociationJoinColumnName('parent'),
            $classMetadata->getColumnName('level'),
            $classMetadata->getColumnName('lft'),
            $classMetadata->getColumnName('rgt'),
            $classMetadata->getSingleIdentifierColumnName(),
            implode(', ', $valuesRows),
        );
    }
}

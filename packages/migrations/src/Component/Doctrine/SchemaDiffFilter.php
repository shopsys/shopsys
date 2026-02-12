<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Component\Doctrine;

use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\TableDiff;

class SchemaDiffFilter
{
    public function getFilteredSchemaDiff(SchemaDiff $schemaDiff): SchemaDiff
    {
        $filteredChangedTables = [];

        foreach ($schemaDiff->getAlteredTables() as $tableDiff) {
            $filteredChangedTables[] = new TableDiff(
                tableName: $tableDiff->getOldTable()?->getName() ?? '',
                addedColumns: $tableDiff->getAddedColumns(),
                modifiedColumns: $tableDiff->getModifiedColumns(),
                droppedColumns: [],
                addedIndexes: $tableDiff->getAddedIndexes(),
                changedIndexes: $tableDiff->getModifiedIndexes(),
                removedIndexes: [],
                fromTable: $tableDiff->getOldTable(),
                addedForeignKeys: $tableDiff->getAddedForeignKeys(),
                changedForeignKeys: $tableDiff->getModifiedForeignKeys(),
                removedForeignKeys: [],
                renamedColumns: $tableDiff->getRenamedColumns(),
                renamedIndexes: $tableDiff->getRenamedIndexes(),
            );
        }

        return new SchemaDiff(
            newTables: $schemaDiff->getCreatedTables(),
            changedTables: $filteredChangedTables,
            removedTables: [],
            createdSchemas: $schemaDiff->getCreatedSchemas(),
            droppedSchemas: [],
            createdSequences: $schemaDiff->getCreatedSequences(),
            alteredSequences: $schemaDiff->getAlteredSequences(),
            droppedSequences: [],
        );
    }
}

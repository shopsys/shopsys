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
                oldTable: $tableDiff->getOldTable(),
                addedColumns: $tableDiff->getAddedColumns(),
                changedColumns: $tableDiff->getChangedColumns(),
                droppedColumns: [],
                addedIndexes: $tableDiff->getAddedIndexes(),
                modifiedIndexes: $tableDiff->getModifiedIndexes(),
                droppedIndexes: [],
                renamedIndexes: $tableDiff->getRenamedIndexes(),
                addedForeignKeys: $tableDiff->getAddedForeignKeys(),
                modifiedForeignKeys: $tableDiff->getModifiedForeignKeys(),
                droppedForeignKeys: [],
            );
        }

        return new SchemaDiff(
            createdSchemas: $schemaDiff->getCreatedSchemas(),
            droppedSchemas: [],
            createdTables: $schemaDiff->getCreatedTables(),
            alteredTables: $filteredChangedTables,
            droppedTables: [],
            createdSequences: $schemaDiff->getCreatedSequences(),
            alteredSequences: $schemaDiff->getAlteredSequences(),
            droppedSequences: [],
        );
    }
}

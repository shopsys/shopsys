<?php

declare(strict_types=1);

namespace Tests\MigrationBundle\Unit\Component\Doctrine;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ColumnDiff;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\Sequence;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;
use Shopsys\MigrationBundle\Component\Doctrine\SchemaDiffFilter;

class SchemaDiffFilterTest extends TestCase
{
    public function testGetFilteredSchemaDiff(): void
    {
        $testType = Type::getType('string');
        $fromTable = new Table('testTableDiff');

        $tableDiff = new TableDiff(
            tableName: 'testTableDiff',
            addedColumns: [new Column('testColumnName1', $testType)],
            modifiedColumns: [new ColumnDiff('testFromColumn', new Column('testColumnName4', $testType), [], new Column('testFromColumn', $testType))],
            droppedColumns: [new Column('testColumnName7', $testType)],
            addedIndexes: [new Index('testIndexName1', ['testColumnName3'])],
            changedIndexes: [new Index('testIndexName2', ['testColumnName6'])],
            removedIndexes: [new Index('testIndexName3', ['testColumnName9'])],
            fromTable: $fromTable,
            addedForeignKeys: [new ForeignKeyConstraint(['testColumnName2'], 'foreignTableName1', [])],
            changedForeignKeys: [new ForeignKeyConstraint(['testColumnName5'], 'foreignTableName2', [])],
            removedForeignKeys: [new ForeignKeyConstraint(['testColumnName8'], 'foreignTableName3', [])],
            renamedColumns: ['oldName' => new Column('testColumnName10', $testType)],
            renamedIndexes: ['oldIndexName' => new Index('testIndexName4', ['testColumnName11'])],
        );

        $schemaDiff = new SchemaDiff(
            newTables: [new Table('testTableName2')],
            changedTables: [$tableDiff],
            removedTables: [new Table('testTableName3')],
            createdSchemas: ['testNamespace1'],
            droppedSchemas: ['testNamespace2'],
            createdSequences: [new Sequence('testSequence2')],
            alteredSequences: [new Sequence('testSequence1')],
            droppedSequences: [new Sequence('testSequence3')],
        );

        $schemaDiffFilter = new SchemaDiffFilter();
        $filteredSchemaDiff = $schemaDiffFilter->getFilteredSchemaDiff($schemaDiff);

        $filteredAlteredTables = $filteredSchemaDiff->getAlteredTables();
        $originalAlteredTables = $schemaDiff->getAlteredTables();

        foreach ($originalAlteredTables as $index => $originalTableDiff) {
            $filteredTableDiff = $filteredAlteredTables[$index];

            $this->assertSame($originalTableDiff->getAddedColumns(), $filteredTableDiff->getAddedColumns());
            $this->assertSame(
                $originalTableDiff->getAddedForeignKeys(),
                $filteredTableDiff->getAddedForeignKeys(),
            );
            $this->assertSame($originalTableDiff->getAddedIndexes(), $filteredTableDiff->getAddedIndexes());
            $this->assertSame($originalTableDiff->getModifiedColumns(), $filteredTableDiff->getModifiedColumns());
            $this->assertSame(
                $originalTableDiff->getModifiedForeignKeys(),
                $filteredTableDiff->getModifiedForeignKeys(),
            );
            $this->assertSame($originalTableDiff->getModifiedIndexes(), $filteredTableDiff->getModifiedIndexes());
            $this->assertSame($originalTableDiff->getRenamedColumns(), $filteredTableDiff->getRenamedColumns());
            $this->assertSame($originalTableDiff->getRenamedIndexes(), $filteredTableDiff->getRenamedIndexes());

            $this->assertEmpty($filteredTableDiff->getDroppedColumns());
            $this->assertEmpty($filteredTableDiff->getDroppedForeignKeys());
            $this->assertEmpty($filteredTableDiff->getDroppedIndexes());
        }

        $this->assertEmpty($filteredSchemaDiff->getDroppedSchemas());
        $this->assertEmpty($filteredSchemaDiff->getDroppedSequences());
        $this->assertEmpty($filteredSchemaDiff->getDroppedTables());

        $this->assertSame($schemaDiff->getAlteredSequences(), $filteredSchemaDiff->getAlteredSequences());
        $this->assertSame($schemaDiff->getCreatedSequences(), $filteredSchemaDiff->getCreatedSequences());
        $this->assertSame($schemaDiff->getCreatedTables(), $filteredSchemaDiff->getCreatedTables());
    }
}

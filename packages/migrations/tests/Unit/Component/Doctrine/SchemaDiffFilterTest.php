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
            oldTable: $fromTable,
            addedColumns: [new Column('testColumnName1', $testType)],
            changedColumns: ['testFromColumn' => new ColumnDiff(new Column('testFromColumn', $testType), new Column('testColumnName4', $testType))],
            droppedColumns: [new Column('testColumnName7', $testType)],
            addedIndexes: [new Index('testIndexName1', ['testColumnName3'])],
            modifiedIndexes: [new Index('testIndexName2', ['testColumnName6'])],
            droppedIndexes: [new Index('testIndexName3', ['testColumnName9'])],
            renamedIndexes: ['oldIndexName' => new Index('testIndexName4', ['testColumnName11'])],
            addedForeignKeys: [new ForeignKeyConstraint(['testColumnName2'], 'foreignTableName1', ['foreignColumn1'])],
            modifiedForeignKeys: [new ForeignKeyConstraint(['testColumnName5'], 'foreignTableName2', ['foreignColumn2'])],
            droppedForeignKeys: [new ForeignKeyConstraint(['testColumnName8'], 'foreignTableName3', ['foreignColumn3'])],
        );

        $schemaDiff = new SchemaDiff(
            createdSchemas: ['testNamespace1'],
            droppedSchemas: ['testNamespace2'],
            createdTables: [new Table('testTableName2')],
            alteredTables: [$tableDiff],
            droppedTables: [new Table('testTableName3')],
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
            $this->assertSame($originalTableDiff->getChangedColumns(), $filteredTableDiff->getChangedColumns());
            $this->assertSame(
                $originalTableDiff->getModifiedForeignKeys(),
                $filteredTableDiff->getModifiedForeignKeys(),
            );
            $this->assertSame($originalTableDiff->getModifiedIndexes(), $filteredTableDiff->getModifiedIndexes());
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

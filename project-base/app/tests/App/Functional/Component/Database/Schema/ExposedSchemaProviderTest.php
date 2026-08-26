<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema;

use Shopsys\McpBundle\Component\Database\Schema\AllowedDatabaseColumnsProvider;
use Shopsys\McpBundle\Component\Database\Schema\AllowedDatabaseTablesProvider;
use Shopsys\McpBundle\Component\Database\Schema\ExposedSchemaProvider;
use Shopsys\McpBundle\Component\Database\Schema\SchemaNameNormalizer;
use Tests\App\Functional\Component\Database\Schema\Model\EmptyQueryableEntity;
use Tests\App\Functional\Component\Database\Schema\Model\InheritedQueryableEntity;
use Tests\App\Functional\Component\Database\Schema\Model\NonQueryableRelationEntity;
use Tests\App\Functional\Component\Database\Schema\Model\ParentAttributedQueryableEntity;
use Tests\App\Functional\Component\Database\Schema\Model\QueryableEntity;
use Tests\App\Functional\Component\Database\Schema\Model\QueryableRelationEntity;

/**
 * @phpstan-import-type SchemaTableArray from \Shopsys\McpBundle\Component\Database\Schema\ExposedSchemaProvider
 */
class ExposedSchemaProviderTest extends AbstractDatabaseSchemaFunctionalTestCase
{
    public function testGenerateExposedSchemaJsonKeepsAllowedColumnsAndOnlyForeignKeysToExposedTables(): void
    {
        $schemaNameNormalizer = new SchemaNameNormalizer($this->em->getConnection());
        $allowedDatabaseTablesProvider = new AllowedDatabaseTablesProvider($this->em, $schemaNameNormalizer);
        $allowedDatabaseColumnsProvider = new AllowedDatabaseColumnsProvider($allowedDatabaseTablesProvider, $schemaNameNormalizer);
        $exposedSchemaProvider = new ExposedSchemaProvider(
            $this->em->getConnection(),
            $allowedDatabaseColumnsProvider,
            $schemaNameNormalizer,
            'dummySchemaFilePath', // irrelevant for this test
        );

        /** @var array<string, SchemaTableArray> $exposedSchema */
        $exposedSchema = json_decode($exposedSchemaProvider->generateExposedSchemaJson(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey(QueryableEntity::TABLE_NAME, $exposedSchema);
        $this->assertArrayHasKey(QueryableRelationEntity::TABLE_NAME, $exposedSchema);
        $this->assertArrayHasKey(InheritedQueryableEntity::TABLE_NAME, $exposedSchema);
        $this->assertArrayHasKey(ParentAttributedQueryableEntity::TABLE_NAME, $exposedSchema);
        $this->assertArrayNotHasKey(EmptyQueryableEntity::TABLE_NAME, $exposedSchema);
        $this->assertArrayNotHasKey(NonQueryableRelationEntity::TABLE_NAME, $exposedSchema);

        $queryableEntityTableSchema = $exposedSchema[QueryableEntity::TABLE_NAME];
        $queryableRelationEntityTableSchema = $exposedSchema[QueryableRelationEntity::TABLE_NAME];
        $inheritedQueryableEntityTableSchema = $exposedSchema[InheritedQueryableEntity::TABLE_NAME];
        $parentAttributedQueryableEntityTableSchema = $exposedSchema[ParentAttributedQueryableEntity::TABLE_NAME];

        $this->assertSame(['id', 'domain_id'], $queryableEntityTableSchema['primaryKey']);
        $this->assertSame(['id'], $queryableRelationEntityTableSchema['primaryKey']);
        $this->assertSame(['id'], $inheritedQueryableEntityTableSchema['primaryKey']);
        $this->assertSame(['id'], $parentAttributedQueryableEntityTableSchema['primaryKey']);

        $this->assertArrayHasKey('queryable_relation_id', $queryableEntityTableSchema['columns']);
        $this->assertArrayHasKey('non_queryable_relation_id', $queryableEntityTableSchema['columns']);
        $this->assertArrayHasKey('visible_value', $queryableEntityTableSchema['columns']);
        $this->assertArrayNotHasKey('blacklisted_queryable_relation_id', $queryableEntityTableSchema['columns']);
        $this->assertArrayNotHasKey('hidden_value', $queryableEntityTableSchema['columns']);
        $this->assertArrayHasKey('id', $inheritedQueryableEntityTableSchema['columns']);
        $this->assertArrayHasKey('locale', $inheritedQueryableEntityTableSchema['columns']);
        $this->assertArrayHasKey('name', $inheritedQueryableEntityTableSchema['columns']);
        $this->assertArrayHasKey('id', $parentAttributedQueryableEntityTableSchema['columns']);
        $this->assertArrayHasKey('code', $parentAttributedQueryableEntityTableSchema['columns']);
        $this->assertArrayHasKey('name', $parentAttributedQueryableEntityTableSchema['columns']);

        $this->assertTrue($this->hasForeignKeyToColumn($queryableEntityTableSchema, 'queryable_relation_id'));
        $this->assertFalse($this->hasForeignKeyToColumn($queryableEntityTableSchema, 'non_queryable_relation_id'));
        $this->assertFalse($this->hasForeignKeyToColumn($queryableEntityTableSchema, 'blacklisted_queryable_relation_id'));
    }

    /**
     * @param SchemaTableArray $tableSchema
     */
    private function hasForeignKeyToColumn(array $tableSchema, string $columnName): bool
    {
        return array_any(
            $tableSchema['foreignKeys'],
            fn (array $foreignKey): bool => in_array($columnName, $foreignKey['columnNames'], true),
        );
    }
}

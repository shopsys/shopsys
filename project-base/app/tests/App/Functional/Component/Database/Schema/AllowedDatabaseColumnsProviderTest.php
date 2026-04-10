<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema;

use Shopsys\McpBundle\Component\Database\Schema\AllowedDatabaseColumnsProvider;
use Tests\App\Functional\Component\Database\Schema\Model\NonQueryableRelationEntity;
use Tests\App\Functional\Component\Database\Schema\Model\ParentAttributedQueryableEntity;
use Tests\App\Functional\Component\Database\Schema\Model\QueryableEntity;
use Tests\App\Functional\Component\Database\Schema\Model\QueryableRelationEntity;

class AllowedDatabaseColumnsProviderTest extends AbstractDatabaseSchemaFunctionalTestCase
{
    /**
     * @inject
     */
    private AllowedDatabaseColumnsProvider $allowedDatabaseColumnsProvider;

    public function testGetAllAllowedColumnsSetIndexedByTableNamesReturnsAllowlistedTablesAndColumns(): void
    {
        $allowedColumnsSetIndexedByTableNames = $this->allowedDatabaseColumnsProvider->getAllAllowedColumnsSetIndexedByTableNames();
        $queryableEntityAllowedColumnsSetIndexedByColumnNames = $allowedColumnsSetIndexedByTableNames[QueryableEntity::TABLE_NAME];

        $this->assertArrayHasKey(QueryableEntity::TABLE_NAME, $allowedColumnsSetIndexedByTableNames);
        $this->assertArrayHasKey(QueryableRelationEntity::TABLE_NAME, $allowedColumnsSetIndexedByTableNames);
        $this->assertArrayNotHasKey(NonQueryableRelationEntity::TABLE_NAME, $allowedColumnsSetIndexedByTableNames);

        $this->assertArrayHasKey('queryable_relation_id', $queryableEntityAllowedColumnsSetIndexedByColumnNames);
        $this->assertArrayHasKey('non_queryable_relation_id', $queryableEntityAllowedColumnsSetIndexedByColumnNames);
        $this->assertArrayHasKey('visible_value', $queryableEntityAllowedColumnsSetIndexedByColumnNames);
        $this->assertArrayNotHasKey('blacklisted_queryable_relation_id', $queryableEntityAllowedColumnsSetIndexedByColumnNames);
        $this->assertArrayNotHasKey('hidden_value', $queryableEntityAllowedColumnsSetIndexedByColumnNames);
    }

    public function testGetAllAllowedColumnsSetIndexedByTableNamesIncludesColumnsInheritedFromMappedSuperclassWithPropertyLevelMcpAttributes(): void
    {
        $allowedColumnsSetIndexedByTableNames = $this->allowedDatabaseColumnsProvider->getAllAllowedColumnsSetIndexedByTableNames();

        $this->assertSame([
            'code' => true,
            'id' => true,
            'name' => true,
        ], $allowedColumnsSetIndexedByTableNames[ParentAttributedQueryableEntity::TABLE_NAME]);
    }
}

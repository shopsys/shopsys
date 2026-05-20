<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema;

use Shopsys\McpBundle\Component\Database\Schema\AllowedDatabaseTablesProvider;
use Tests\App\Functional\Component\Database\Schema\Model\NonQueryableRelationEntity;
use Tests\App\Functional\Component\Database\Schema\Model\QueryableEntity;
use Tests\App\Functional\Component\Database\Schema\Model\QueryableRelationEntity;

class AllowedDatabaseTablesProviderTest extends AbstractDatabaseSchemaFunctionalTestCase
{
    /**
     * @inject
     */
    private AllowedDatabaseTablesProvider $allowedDatabaseTablesProvider;

    public function testGetAllAllowedClassMetadataByTableNamesReturnsOnlyAllowlistedTables(): void
    {
        $allowedClassMetadataIndexedByTableNames = $this->allowedDatabaseTablesProvider->getAllAllowedClassMetadataIndexedByTableNames();

        $this->assertArrayHasKey(QueryableEntity::TABLE_NAME, $allowedClassMetadataIndexedByTableNames);
        $this->assertArrayHasKey(QueryableRelationEntity::TABLE_NAME, $allowedClassMetadataIndexedByTableNames);
        $this->assertArrayNotHasKey(NonQueryableRelationEntity::TABLE_NAME, $allowedClassMetadataIndexedByTableNames);
    }
}

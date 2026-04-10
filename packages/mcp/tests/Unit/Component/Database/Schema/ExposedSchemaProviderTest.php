<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\Database\Schema;

use Doctrine\DBAL\Connection;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\Component\Database\Schema\AllowedDatabaseColumnsProvider;
use Shopsys\McpBundle\Component\Database\Schema\AllowedDatabaseTablesProvider;
use Shopsys\McpBundle\Component\Database\Schema\ExposedSchemaProvider;
use Shopsys\McpBundle\Component\Database\Schema\SchemaNameNormalizer;

class ExposedSchemaProviderTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private const array STORED_EXPOSED_SCHEMA = [
        'products' => [
            'primaryKey' => ['id'],
            'columns' => [
                'id' => ['dataType' => 'integer', 'nullable' => false],
                'name' => ['dataType' => 'text', 'nullable' => false],
            ],
            'foreignKeys' => [],
        ],
        'product_translations' => [
            'primaryKey' => ['id'],
            'columns' => [
                'id' => ['dataType' => 'integer', 'nullable' => false],
                'locale' => ['dataType' => 'text', 'nullable' => false],
            ],
            'foreignKeys' => [],
        ],
    ];

    /**
     * @var array<int, string>
     */
    private array $temporarySchemaFilePaths = [];

    #[Override]
    protected function tearDown(): void
    {
        foreach ($this->temporarySchemaFilePaths as $temporarySchemaFilePath) {
            if (is_file($temporarySchemaFilePath)) {
                unlink($temporarySchemaFilePath);
            }
        }

        parent::tearDown();
    }

    public function testGetExposedSchemaReturnsAllTablesOrOnlyRequestedTables(): void
    {
        $exposedSchemaProvider = $this->createExposedSchemaProvider();

        $allExposedSchema = $exposedSchemaProvider->getExposedSchema([]);
        $filteredExposedSchema = $exposedSchemaProvider->getExposedSchema(['product_translations', 'missing_table']);

        $this->assertSame(['products', 'product_translations'], array_keys($allExposedSchema));
        $this->assertSame(['product_translations'], array_keys($filteredExposedSchema));
    }

    public function testGetExposedTableNamesAndAllowedColumnsSetIndexedByTableNamesReturnStoredSchemaProjection(): void
    {
        $exposedSchemaProvider = $this->createExposedSchemaProvider();

        $exposedTableNames = $exposedSchemaProvider->getExposedTableNames();
        $allowedColumnsSetIndexedByTableNames = $exposedSchemaProvider->getAllowedColumnsSetIndexedByTableNames();

        $this->assertSame(['products', 'product_translations'], $exposedTableNames);
        $this->assertSame([
            'products' => [
                'id' => true,
                'name' => true,
            ],
            'product_translations' => [
                'id' => true,
                'locale' => true,
            ],
        ], $allowedColumnsSetIndexedByTableNames);
    }

    private function createExposedSchemaProvider(): ExposedSchemaProvider
    {
        return new ExposedSchemaProvider(
            $this->createStub(Connection::class),
            $this->createStub(AllowedDatabaseTablesProvider::class),
            $this->createStub(AllowedDatabaseColumnsProvider::class),
            $this->createStub(SchemaNameNormalizer::class),
            $this->createTemporarySchemaFile(),
        );
    }

    private function createTemporarySchemaFile(): string
    {
        $temporarySchemaFilePath = tempnam(sys_get_temp_dir(), 'mcp-schema-');

        self::assertNotFalse($temporarySchemaFilePath);
        $this->temporarySchemaFilePaths[] = $temporarySchemaFilePath;

        file_put_contents(
            $temporarySchemaFilePath,
            json_encode(self::STORED_EXPOSED_SCHEMA, JSON_THROW_ON_ERROR),
        );

        return $temporarySchemaFilePath;
    }
}

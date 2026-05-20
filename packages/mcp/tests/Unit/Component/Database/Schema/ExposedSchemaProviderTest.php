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
    private const string SCHEMA_FILE_NAME = 'mcp-schema.json';

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

    private ?string $temporarySchemaDirectory = null;

    #[Override]
    protected function tearDown(): void
    {
        if ($this->temporarySchemaDirectory !== null) {
            $temporarySchemaFilePath = $this->temporarySchemaDirectory . '/' . self::SCHEMA_FILE_NAME;

            if (is_file($temporarySchemaFilePath)) {
                unlink($temporarySchemaFilePath);
            }

            if (is_dir($this->temporarySchemaDirectory)) {
                rmdir($this->temporarySchemaDirectory);
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
        $temporarySchemaDirectory = $this->createTemporarySchemaDirectoryWithSchemaFile();

        return new ExposedSchemaProvider(
            $this->createStub(Connection::class),
            $this->createStub(AllowedDatabaseTablesProvider::class),
            $this->createStub(AllowedDatabaseColumnsProvider::class),
            $this->createStub(SchemaNameNormalizer::class),
            $temporarySchemaDirectory,
        );
    }

    private function createTemporarySchemaDirectoryWithSchemaFile(): string
    {
        $temporarySchemaDirectory = sys_get_temp_dir() . '/mcp-schema-' . bin2hex(random_bytes(8));
        mkdir($temporarySchemaDirectory);
        $temporarySchemaFilePath = $temporarySchemaDirectory . '/' . self::SCHEMA_FILE_NAME;

        $this->temporarySchemaDirectory = $temporarySchemaDirectory;

        file_put_contents(
            $temporarySchemaFilePath,
            json_encode(self::STORED_EXPOSED_SCHEMA, JSON_THROW_ON_ERROR),
        );

        return $temporarySchemaDirectory;
    }
}

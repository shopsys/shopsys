<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Elasticsearch;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchCannotReadDefinitionFileException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchInvalidJsonInDefinitionFileException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionModifier;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionYamlResolver;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;

class IndexDefinitionTest extends TestCase
{
    #[DataProvider('indexDefinitionParametersForIndexAlias')]
    public function testGetIndexAlias(
        string $indexName,
        string $definitionsDirectory,
        string $indexPrefix,
        int $domainId,
        string $expectedResult,
    ): void {
        $indexDefinition = $this->createIndexDefinition($indexName, $definitionsDirectory, $indexPrefix, $domainId, EnvironmentType::TEST);
        $this->assertSame($expectedResult, $indexDefinition->getIndexAlias());
    }

    public static function indexDefinitionParametersForIndexAlias(): array
    {
        return [
            ['product', '', '', 1, 'product_1'],
            ['product', '', '', 2, 'product_2'],
            ['product', '', 'prefixed', 1, 'prefixed_product_1'],
            ['category', '', '', 1, 'category_1'],
            ['product', '', 'pre', 2, 'pre_product_2'],
        ];
    }

    public function testGetDefinitionReturnsDefinition(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/valid/';
        $indexDefinition = $this->createIndexDefinition('product', $definitionDirectory, '', 1, EnvironmentType::PRODUCTION);
        $this->assertSame(['foo' => 'bar'], $indexDefinition->getDefinition());
    }

    public function testGetDefinitionOnInvalidJsonThrowsException(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/invalidJson/';
        $indexDefinition = $this->createIndexDefinition('product', $definitionDirectory, '', 1, EnvironmentType::PRODUCTION);

        $this->expectException(ElasticsearchInvalidJsonInDefinitionFileException::class);
        $indexDefinition->getDefinition();
    }

    public function testGetDefinitionOnNonExistingDefinitionThrowsException(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/non-existing-folder-id-3e85ba/';
        $indexDefinition = $this->createIndexDefinition('product', $definitionDirectory, '', 1, EnvironmentType::PRODUCTION);

        $this->expectException(ElasticsearchCannotReadDefinitionFileException::class);
        $indexDefinition->getDefinition();
    }

    public function testGetVersionedIndexName(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/valid/';
        $indexDefinition = $this->createIndexDefinition('product', $definitionDirectory, '', 1, EnvironmentType::PRODUCTION);

        $this->assertSame('product_1_49a3696adf0fbfacc12383a2d7400d51', $indexDefinition->getVersionedIndexName());
    }

    public function testDevEnvironmentIsLimited(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/valid/';
        $indexDefinition = $this->createIndexDefinition('product', $definitionDirectory, '', 1, EnvironmentType::DEVELOPMENT);
        $this->assertSame(
            [
                'foo' => 'bar',
                'settings' => [
                    'index' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 0,
                    ],
                ],
            ],
            $indexDefinition->getDefinition(),
        );
    }

    public function testProdEnvironmentIsLimitedWhenForced(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/valid/';
        $indexDefinition = new IndexDefinition(
            'product',
            $definitionDirectory,
            '',
            1,
            new IndexDefinitionModifier(EnvironmentType::PRODUCTION, true),
            new IndexDefinitionYamlResolver(),
        );
        $this->assertSame(
            [
                'foo' => 'bar',
                'settings' => [
                    'index' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 0,
                    ],
                ],
            ],
            $indexDefinition->getDefinition(),
        );
    }

    private function createIndexDefinition(
        string $indexName,
        string $definitionsDirectory,
        string $indexPrefix,
        int $domainId,
        string $environment,
        bool $forceElasticLimit = false,
    ): IndexDefinition {
        return new IndexDefinition(
            $indexName,
            $definitionsDirectory,
            $indexPrefix,
            $domainId,
            new IndexDefinitionModifier($environment, $forceElasticLimit),
            new IndexDefinitionYamlResolver(),
        );
    }
}

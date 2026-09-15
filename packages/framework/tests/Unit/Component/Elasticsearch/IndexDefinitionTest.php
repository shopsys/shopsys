<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Elasticsearch;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchCannotReadDefinitionFileException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchInvalidJsonInDefinitionFileException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionModifier;
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
        $indexDefinition = new IndexDefinition($indexName, $definitionsDirectory, $indexPrefix, $domainId, new IndexDefinitionModifier(EnvironmentType::TEST, false));
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

    /**
     * @return iterable<string, array{indexPrefix: string, domainId: int, indexName: string, expectedResult: bool}>
     */
    public static function getIsVersionedIndexNameOfData(): iterable
    {
        yield 'current mapping hash' => [
            'indexPrefix' => '',
            'domainId' => 1,
            'indexName' => 'product_1_' . md5(serialize(['foo' => 'bar'])),
            'expectedResult' => true,
        ];

        yield 'stale mapping hash' => [
            'indexPrefix' => '',
            'domainId' => 1,
            'indexName' => 'product_1_c13d8712c2e1f24b29d873acc70044f5',
            'expectedResult' => true,
        ];

        yield 'prefixed index' => [
            'indexPrefix' => 'project-prefix',
            'domainId' => 1,
            'indexName' => 'project-prefix_product_1_c13d8712c2e1f24b29d873acc70044f5',
            'expectedResult' => true,
        ];

        yield 'same index of another domain sharing the digit prefix' => [
            'indexPrefix' => '',
            'domainId' => 1,
            'indexName' => 'product_10_c13d8712c2e1f24b29d873acc70044f5',
            'expectedResult' => false,
        ];

        yield 'another index' => [
            'indexPrefix' => '',
            'domainId' => 1,
            'indexName' => 'category_1_c13d8712c2e1f24b29d873acc70044f5',
            'expectedResult' => false,
        ];

        yield 'unprefixed index while prefix is configured' => [
            'indexPrefix' => 'project-prefix',
            'domainId' => 1,
            'indexName' => 'product_1_c13d8712c2e1f24b29d873acc70044f5',
            'expectedResult' => false,
        ];
    }

    #[DataProvider('getIsVersionedIndexNameOfData')]
    public function testIsVersionedIndexNameOfMatchesAnyMappingHashOfTheSameAlias(
        string $indexPrefix,
        int $domainId,
        string $indexName,
        bool $expectedResult,
    ): void {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/valid/';
        $indexDefinition = new IndexDefinition('product', $definitionDirectory, $indexPrefix, $domainId, new IndexDefinitionModifier(EnvironmentType::PRODUCTION, false));

        $this->assertSame($expectedResult, $indexDefinition->isVersionedIndexNameOf($indexName));
    }

    public function testGetDefinitionReturnsDefinition(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/valid/';
        $indexDefinition = new IndexDefinition('product', $definitionDirectory, '', 1, new IndexDefinitionModifier(EnvironmentType::PRODUCTION, false));
        $this->assertSame(['foo' => 'bar'], $indexDefinition->getDefinition());
    }

    public function testGetDefinitionOnInvalidJsonThrowsException(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/invalidJson/';
        $indexDefinition = new IndexDefinition('product', $definitionDirectory, '', 1, new IndexDefinitionModifier(EnvironmentType::PRODUCTION, false));

        $this->expectException(ElasticsearchInvalidJsonInDefinitionFileException::class);
        $indexDefinition->getDefinition();
    }

    public function testGetDefinitionOnNonExistingDefinitionThrowsException(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/non-existing-folder-id-3e85ba/';
        $indexDefinition = new IndexDefinition('product', $definitionDirectory, '', 1, new IndexDefinitionModifier(EnvironmentType::PRODUCTION, false));

        $this->expectException(ElasticsearchCannotReadDefinitionFileException::class);
        $indexDefinition->getDefinition();
    }

    public function testGetVersionedIndexName(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/valid/';
        $indexDefinition = new IndexDefinition('product', $definitionDirectory, '', 1, new IndexDefinitionModifier(EnvironmentType::PRODUCTION, false));

        $this->assertSame('product_1_49a3696adf0fbfacc12383a2d7400d51', $indexDefinition->getVersionedIndexName());
    }

    public function testDevEnvironmentIsLimited(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/valid/';
        $indexDefinition = new IndexDefinition('product', $definitionDirectory, '', 1, new IndexDefinitionModifier(EnvironmentType::DEVELOPMENT, false));
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
        $indexDefinition = new IndexDefinition('product', $definitionDirectory, '', 1, new IndexDefinitionModifier(EnvironmentType::PRODUCTION, true));
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
}

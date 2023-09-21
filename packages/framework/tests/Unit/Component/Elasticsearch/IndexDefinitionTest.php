<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Elasticsearch;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionModifier;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;

class IndexDefinitionTest extends TestCase
{
    /**
     * @param string $indexName
     * @param string $definitionsDirectory
     * @param string $indexPrefix
     * @param int $domainId
     * @param string $expectedResult
     * @dataProvider indexDefinitionParametersForIndexAlias
     */
    public function testGetIndexAlias(
        string $indexName,
        string $definitionsDirectory,
        string $indexPrefix,
        int $domainId,
        string $expectedResult,
    ) {
        $indexDefinition = new IndexDefinition($indexName, $definitionsDirectory, $indexPrefix, $domainId, new IndexDefinitionModifier(EnvironmentType::TEST, false));
        $this->assertSame($expectedResult, $indexDefinition->getIndexAlias());
    }

    /**
     * @return array
     */
    public function indexDefinitionParametersForIndexAlias(): array
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
        $indexDefinition = new IndexDefinition('product', $definitionDirectory, '', 1, new IndexDefinitionModifier(EnvironmentType::PRODUCTION, false));
        $this->assertSame(['foo' => 'bar'], $indexDefinition->getDefinition());
    }

    public function testGetDefinitionOnInvalidJsonThrowsException(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/invalidJson/';
        $indexDefinition = new IndexDefinition('product', $definitionDirectory, '', 1, new IndexDefinitionModifier(EnvironmentType::PRODUCTION, false));

        $this->expectException(ElasticsearchIndexException::class);
        $this->expectExceptionMessage('Invalid JSON in "product" definition file');
        $indexDefinition->getDefinition();
    }

    public function testGetDefinitionOnNonExistingDefinitionThrowsException(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/non-existing-folder-id-3e85ba/';
        $indexDefinition = new IndexDefinition('product', $definitionDirectory, '', 1, new IndexDefinitionModifier(EnvironmentType::PRODUCTION, false));

        $this->expectException(ElasticsearchIndexException::class);
        $this->expectExceptionMessage('Can\'t read definition file at path');
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

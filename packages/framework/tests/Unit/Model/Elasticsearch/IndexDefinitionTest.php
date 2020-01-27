<?php

namespace Shopsys\FrameworkBundle\Model\Elasticsearch;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Elasticsearch\Exception\ElasticsearchIndexException;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Tests\FrameworkBundle\Unit\Model\Elasticsearch\__fixtures\CategoryIndex;

class IndexDefinitionTest extends TestCase
{
    /**
     * @param AbstractIndex $index
     * @param string $definitionsDirectory
     * @param string $indexPrefix
     * @param int $domainId
     * @param string $expectedResult
     * @dataProvider indexDefinitionParametersForIndexAlias
     */
    public function testGetIndexAlias(
        AbstractIndex $index,
        string $definitionsDirectory,
        string $indexPrefix,
        int $domainId,
        string $expectedResult
    ) {
        $indexDefinition = new IndexDefinition($index, $definitionsDirectory, $indexPrefix, $domainId);
        $this->assertSame($expectedResult, $indexDefinition->getIndexAlias());
    }

    public function indexDefinitionParametersForIndexAlias(): array
    {
        return [
            [new ProductIndex(), '', '', 1, 'product_1'],
            [new ProductIndex(), '', '', 2, 'product_2'],
            [new ProductIndex(), '', 'prefixed', 1, 'prefixed_product_1'],
            [new CategoryIndex(), '', '', 1, 'category_1'],
            [new ProductIndex(), '', 'pre', 2, 'pre_product_2'],
        ];
    }

    public function testGetDefinitionReturnsDefinition(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/valid/';
        $indexDefinition = new IndexDefinition(new ProductIndex(), $definitionDirectory, '', 1);
        $this->assertSame(['foo' => 'bar'], $indexDefinition->getDefinition());
    }

    public function testGetDefinitionOnInvalidJsonThrowsException(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/invalidJson/';
        $indexDefinition = new IndexDefinition(new ProductIndex(), $definitionDirectory, '', 1);

        $this->expectException(ElasticsearchIndexException::class);
        $this->expectExceptionMessage('Invalid JSON in product definition file');
        $indexDefinition->getDefinition();
    }

    public function testGetDefinitionOnNonExistingDefinitionThrowsException(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/non-existing-folder-id-3e85ba/';
        $indexDefinition = new IndexDefinition(new ProductIndex(), $definitionDirectory, '', 1);

        $this->expectException(ElasticsearchIndexException::class);
        $this->expectExceptionMessage('Can\'t read definition file at path');
        $indexDefinition->getDefinition();
    }

    public function testGetVersionedIndexName(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/valid/';
        $indexDefinition = new IndexDefinition(new ProductIndex(), $definitionDirectory, '', 1);

        $this->assertSame('product_1_49a3696adf0fbfacc12383a2d7400d51', $indexDefinition->getVersionedIndexName());
    }
}

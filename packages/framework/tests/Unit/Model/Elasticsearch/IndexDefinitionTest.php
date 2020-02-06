<?php

namespace Tests\FrameworkBundle\Unit\Model\Elasticsearch;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Tests\FrameworkBundle\Unit\Model\Elasticsearch\__fixtures\CategoryIndex;

class IndexDefinitionTest extends TestCase
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex $index
     * @param string $definitionsDirectory
     * @param string $indexPrefix
     * @param int $domainId
     * @param string $expectedResult
     *
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

    /**
     * @return array
     */
    public function indexDefinitionParametersForIndexAlias(): array
    {
        $productIndexMock = $this->getBasicProductIndexMock();

        /** @var \Tests\FrameworkBundle\Unit\Model\Elasticsearch\__fixtures\CategoryIndex|\PHPUnit\Framework\MockObject\MockObject $categoryIndexMock */
        $categoryIndexMock = $this->createMock(CategoryIndex::class);
        $categoryIndexMock->method('getName')->willReturn('category');

        return [
            [$productIndexMock, '', '', 1, 'product_1'],
            [$productIndexMock, '', '', 2, 'product_2'],
            [$productIndexMock, '', 'prefixed', 1, 'prefixed_product_1'],
            [$categoryIndexMock, '', '', 1, 'category_1'],
            [$productIndexMock, '', 'pre', 2, 'pre_product_2'],
        ];
    }

    public function testGetDefinitionReturnsDefinition(): void
    {
        $productIndexMock = $this->getBasicProductIndexMock();

        $definitionDirectory = __DIR__ . '/__fixtures/definitions/valid/';
        $indexDefinition = new IndexDefinition($productIndexMock, $definitionDirectory, '', 1);
        $this->assertSame(['foo' => 'bar'], $indexDefinition->getDefinition());
    }

    public function testGetDefinitionOnInvalidJsonThrowsException(): void
    {
        $productIndexMock = $this->getBasicProductIndexMock();

        $definitionDirectory = __DIR__ . '/__fixtures/definitions/invalidJson/';
        $indexDefinition = new IndexDefinition($productIndexMock, $definitionDirectory, '', 1);

        $this->expectException(ElasticsearchIndexException::class);
        $this->expectExceptionMessage('Invalid JSON in "product" definition file');
        $indexDefinition->getDefinition();
    }

    public function testGetDefinitionOnNonExistingDefinitionThrowsException(): void
    {
        $productIndexMock = $this->getBasicProductIndexMock();

        $definitionDirectory = __DIR__ . '/__fixtures/definitions/non-existing-folder-id-3e85ba/';
        $indexDefinition = new IndexDefinition($productIndexMock, $definitionDirectory, '', 1);

        $this->expectException(ElasticsearchIndexException::class);
        $this->expectExceptionMessage('Can\'t read definition file at path');
        $indexDefinition->getDefinition();
    }

    public function testGetVersionedIndexName(): void
    {
        $productIndexMock = $this->getBasicProductIndexMock();

        $definitionDirectory = __DIR__ . '/__fixtures/definitions/valid/';
        $indexDefinition = new IndexDefinition($productIndexMock, $definitionDirectory, '', 1);

        $this->assertSame('product_1_49a3696adf0fbfacc12383a2d7400d51', $indexDefinition->getVersionedIndexName());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex
     */
    private function getBasicProductIndexMock(): ProductIndex
    {
        $productIndexMock = $this->createMock(ProductIndex::class);
        $productIndexMock->method('getName')->willReturn('product');

        return $productIndexMock;
    }
}

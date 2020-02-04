<?php

namespace Tests\FrameworkBundle\Unit\Model\Elasticsearch;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductDataProvider;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Tests\FrameworkBundle\Unit\Model\Elasticsearch\__fixtures\CategoryDataProvider;
use Tests\FrameworkBundle\Unit\Model\Elasticsearch\__fixtures\CategoryIndex;

class IndexDefinitionTest extends TestCase
{
    /**
     * @return \Tests\FrameworkBundle\Unit\Model\Elasticsearch\__fixtures\CategoryDataProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getCategoryDataProviderMock(): CategoryDataProvider
    {
        return $this->createMock(CategoryDataProvider::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductDataProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getProductDataProviderMock(): ProductDataProvider
    {
        return $this->createMock(ProductDataProvider::class);
    }

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
        $productIndex = new ProductIndex($this->getProductDataProviderMock());
        $categoryIndex = new CategoryIndex($this->getCategoryDataProviderMock());
        return [
            [$productIndex, '', '', 1, 'product_1'],
            [$productIndex, '', '', 2, 'product_2'],
            [$productIndex, '', 'prefixed', 1, 'prefixed_product_1'],
            [$categoryIndex, '', '', 1, 'category_1'],
            [$productIndex, '', 'pre', 2, 'pre_product_2'],
        ];
    }

    public function testGetDefinitionReturnsDefinition(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/valid/';
        $productIndex = new ProductIndex($this->getProductDataProviderMock());
        $indexDefinition = new IndexDefinition($productIndex, $definitionDirectory, '', 1);
        $this->assertSame(['foo' => 'bar'], $indexDefinition->getDefinition());
    }

    public function testGetDefinitionOnInvalidJsonThrowsException(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/invalidJson/';
        $productIndex = new ProductIndex($this->getProductDataProviderMock());
        $indexDefinition = new IndexDefinition($productIndex, $definitionDirectory, '', 1);

        $this->expectException(ElasticsearchIndexException::class);
        $this->expectExceptionMessage('Invalid JSON in "product" definition file');
        $indexDefinition->getDefinition();
    }

    public function testGetDefinitionOnNonExistingDefinitionThrowsException(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/non-existing-folder-id-3e85ba/';
        $productIndex = new ProductIndex($this->getProductDataProviderMock());
        $indexDefinition = new IndexDefinition($productIndex, $definitionDirectory, '', 1);

        $this->expectException(ElasticsearchIndexException::class);
        $this->expectExceptionMessage('Can\'t read definition file at path');
        $indexDefinition->getDefinition();
    }

    public function testGetVersionedIndexName(): void
    {
        $definitionDirectory = __DIR__ . '/__fixtures/definitions/valid/';
        $productIndex = new ProductIndex($this->getProductDataProviderMock());
        $indexDefinition = new IndexDefinition($productIndex, $definitionDirectory, '', 1);

        $this->assertSame('product_1_49a3696adf0fbfacc12383a2d7400d51', $indexDefinition->getVersionedIndexName());
    }
}

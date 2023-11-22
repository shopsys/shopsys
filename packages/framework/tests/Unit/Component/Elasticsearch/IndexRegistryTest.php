<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Elasticsearch;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Tests\FrameworkBundle\Unit\Component\Elasticsearch\__fixtures\CategoryIndex;

class IndexRegistryTest extends TestCase
{
    protected IndexRegistry $indexRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domainMock */
        $domainMock = $this->createMock(Domain::class);
        /** @var \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository $productExportRepositoryMock */
        $productExportRepositoryMock = $this->createMock(ProductExportRepository::class);

        $this->indexRegistry = new IndexRegistry([
            new ProductIndex($domainMock, $productExportRepositoryMock),
            new CategoryIndex(),
        ]);
    }

    /**
     * @dataProvider registeredIndexProvider
     * @param string $indexName
     * @param bool $isRegistered
     */
    public function testIsIndexRegistered(string $indexName, bool $isRegistered): void
    {
        $this->assertSame($isRegistered, $this->indexRegistry->isIndexRegistered($indexName));
    }

    /**
     * @return 'category'[]|'product'[]|true[][]|'price'[]|false[][]
     */
    public function registeredIndexProvider(): array
    {
        return [
            ['product', true],
            ['category', true],
            ['price', false],
        ];
    }

    public function testGetIndexByIndexNameSuccess(): void
    {
        $productIndex = $this->indexRegistry->getIndexByIndexName('product');
        $this->assertInstanceOf(ProductIndex::class, $productIndex);

        $categoryIndex = $this->indexRegistry->getIndexByIndexName('category');
        $this->assertInstanceOf(CategoryIndex::class, $categoryIndex);
    }

    public function testGetIndexByIndexNameWithNonRegisteredNameThrowsException(): void
    {
        $this->expectException(ElasticsearchIndexException::class);
        $this->expectExceptionMessage('There is no index "price" registered');
        $this->indexRegistry->getIndexByIndexName('price');
    }

    public function testGetRegisteredIndexNames(): void
    {
        $registeredIndexNames = $this->indexRegistry->getRegisteredIndexNames();
        $this->assertCount(2, $registeredIndexNames);
        $this->assertSame('product', $registeredIndexNames[0]);
        $this->assertSame('category', $registeredIndexNames[1]);
    }

    public function testGetRegisteredIndexes(): void
    {
        $registeredIndexes = $this->indexRegistry->getRegisteredIndexes();
        $this->assertCount(2, $registeredIndexes);
        $this->assertArrayHasKey('product', $registeredIndexes);
        $this->assertInstanceOf(ProductIndex::class, $registeredIndexes['product']);
        $this->assertArrayHasKey('category', $registeredIndexes);
        $this->assertInstanceOf(CategoryIndex::class, $registeredIndexes['category']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\ProductsBlock;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class ProductsBlockTest extends AbstractWebBlockTestCase
{
    public function testAtLeastOneProductIsRequired(): void
    {
        $block = new ProductsBlock($this->createStub(ProductRepository::class));

        $this->assertNotSame([], ContentEditorTestFactory::validateField($block, 'productIds', []));
        $this->assertSame([], ContentEditorTestFactory::validateField($block, 'productIds', [1]));
    }

    public function testExportAddsCatnumsInOrderOfIdsAndDropsMissingProducts(): void
    {
        $productRepository = $this->createStub(ProductRepository::class);
        $productRepository->method('getAllByIds')->willReturn([
            $this->createProduct(1, 'A'),
            $this->createProduct(2, 'B'),
            $this->createProduct(4, null),
        ]);
        $block = new ProductsBlock($productRepository);

        $result = $block->export(['productIds' => [2, 3, 4, 1]], 1);

        $this->assertSame(['B', 'A'], $result['catnums']);
        $this->assertSame([2, 3, 4, 1], $result['productIds']);
    }

    public function testExtractTextIsEmpty(): void
    {
        $block = new ProductsBlock($this->createStub(ProductRepository::class));

        $this->assertSame('', $block->extractText(['productIds' => [1]]));
    }

    private function createProduct(int $id, ?string $catnum): Product
    {
        $product = $this->createStub(Product::class);
        $product->method('getId')->willReturn($id);
        $product->method('getCatnum')->willReturn($catnum);

        return $product;
    }
}

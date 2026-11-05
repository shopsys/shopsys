<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Transformers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Form\Transformers\ProductIdToProductTransformer;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProductIdToProductTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $productId = 1;
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->atLeastOnce())->method('getId')->willReturn($productId);

        $productRepository = $this->createStub(ProductRepository::class);
        $productIdToProductTransformer = new ProductIdToProductTransformer($productRepository);

        $this->assertSame($productId, $productIdToProductTransformer->transform($product));
        $this->assertNull($productIdToProductTransformer->transform(null));
    }

    public function testReverseTransform(): void
    {
        $productId = 1;
        $product = $this->createStub(Product::class);

        $productsRepositoryGetByIdValues = [
            [$productId, $product],
        ];

        $productRepository = $this->getMockBuilder(ProductRepository::class)
            ->onlyMethods(['getById'])
            ->disableOriginalConstructor()
            ->getMock();
        $productRepository->expects($this->atLeastOnce())->method('getById')->willReturnMap(
            $productsRepositoryGetByIdValues,
        );

        $productIdToProductTransformer = new ProductIdToProductTransformer($productRepository);

        $this->assertSame($product, $productIdToProductTransformer->reverseTransform($productId));
        $this->assertNull($productIdToProductTransformer->reverseTransform(null));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function identifierNoProductCanHaveDataProvider(): array
    {
        return [
            'beyond the integer column' => ['2147483648'],
            'beyond the integer of PHP' => ['99999999999999999999'],
            'negative' => ['-5'],
        ];
    }

    /**
     * The database refuses to look up an identifier its column cannot hold rather than finding nothing, so such
     * an identifier (edited by hand) is refused right away.
     */
    #[DataProvider('identifierNoProductCanHaveDataProvider')]
    public function testIdentifierNoProductCanHaveIsNeverLookedUp(string $productId): void
    {
        $productRepositoryMock = $this->createMock(ProductRepository::class);
        $productRepositoryMock->expects($this->never())->method('getById');

        $this->expectException(TransformationFailedException::class);

        (new ProductIdToProductTransformer($productRepositoryMock))->reverseTransform($productId);
    }
}

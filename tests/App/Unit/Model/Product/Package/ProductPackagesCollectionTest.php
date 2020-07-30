<?php

declare(strict_types=1);

namespace Tests\App\Unit\Model\Product\Package;

use App\Model\Product\Package\ProductPackage;
use App\Model\Product\Package\ProductPackagesCollection;
use App\Model\Product\Product;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;

class ProductPackagesCollectionTest extends TestCase
{
    public function testTotalPackagesCount(): void
    {
        $product1 = $this->createProduct(1);
        $product2 = $this->createProduct(2);
        $productPackages = [
            $this->createProductPackage($product1, null, null, null, null),
            $this->createProductPackage($product1, null, null, null, null),
            $this->createProductPackage($product2, null, null, null, null),
        ];
        $quantifiedProducts = [
            new QuantifiedProduct($product1, 2),
            new QuantifiedProduct($product2, 1),
        ];

        $productPackagesCollection = new ProductPackagesCollection($quantifiedProducts, $productPackages);

        self::assertSame(5, $productPackagesCollection->getTotalPackagesCount()); // 2 * 2 + 1
    }

    public function testWeightSum(): void
    {
        $product1 = $this->createProduct(1);
        $product2 = $this->createProduct(2);
        $productPackages = [
            $this->createProductPackage($product1, 10.0, null, null, null),
            $this->createProductPackage($product1, 1.0, null, null, null),
            $this->createProductPackage($product2, 5.0, null, null, null),
        ];
        $quantifiedProducts = [
            new QuantifiedProduct($product1, 2),
            new QuantifiedProduct($product2, 1),
        ];

        $productPackagesCollection = new ProductPackagesCollection($quantifiedProducts, $productPackages);

        self::assertEqualsWithDelta(27.0, $productPackagesCollection->getWeightSum(), 0.01); // 2 * (10 + 1) + 5
    }

    public function testTopDimension1WithUnsortedDimensions(): void
    {
        $product1 = $this->createProduct(1);
        $product2 = $this->createProduct(2);
        $productPackages = [
            $this->createProductPackage($product1, null, 7, 9, 8),
            $this->createProductPackage($product1, null, 5, 6, 4),
            $this->createProductPackage($product2, null, 1, 2, 3),
        ];
        $quantifiedProducts = [
            new QuantifiedProduct($product1, 2),
            new QuantifiedProduct($product2, 1),
        ];

        $productPackagesCollection = new ProductPackagesCollection($quantifiedProducts, $productPackages);

        self::assertSame(9, $productPackagesCollection->getTopDimension1()); // max(9, 6, 3)
    }

    public function testTopDimension2WithUnsortedDimensions(): void
    {
        $product1 = $this->createProduct(1);
        $product2 = $this->createProduct(2);
        $productPackages = [
            $this->createProductPackage($product1, null, 7, 9, 8),
            $this->createProductPackage($product1, null, 5, 6, 4),
            $this->createProductPackage($product2, null, 1, 2, 3),
        ];
        $quantifiedProducts = [
            new QuantifiedProduct($product1, 2),
            new QuantifiedProduct($product2, 1),
        ];

        $productPackagesCollection = new ProductPackagesCollection($quantifiedProducts, $productPackages);

        self::assertSame(8, $productPackagesCollection->getTopDimension2()); // max(8, 5, 2)
    }

    public function testDimension3SumWithUnsortedDimensions(): void
    {
        $product1 = $this->createProduct(1);
        $product2 = $this->createProduct(2);
        $productPackages = [
            $this->createProductPackage($product1, null, 7, 9, 8),
            $this->createProductPackage($product1, null, 5, 6, 4),
            $this->createProductPackage($product2, null, 1, 2, 3),
        ];
        $quantifiedProducts = [
            new QuantifiedProduct($product1, 2),
            new QuantifiedProduct($product2, 1),
        ];

        $productPackagesCollection = new ProductPackagesCollection($quantifiedProducts, $productPackages);

        self::assertSame(23, $productPackagesCollection->getDimension3Sum()); // sum(7, 7, 4, 4, 1)
    }

    public function testTotalGirthWithUnsortedDimensions(): void
    {
        $product1 = $this->createProduct(1);
        $product2 = $this->createProduct(2);
        $productPackages = [
            $this->createProductPackage($product1, null, 7, 10, 9),
            $this->createProductPackage($product1, null, 5, 6, 4),
            $this->createProductPackage($product2, null, 1, 2, 3),
        ];
        $quantifiedProducts = [
            new QuantifiedProduct($product1, 2),
            new QuantifiedProduct($product2, 1),
        ];

        $productPackagesCollection = new ProductPackagesCollection($quantifiedProducts, $productPackages);

        self::assertSame(76, $productPackagesCollection->getTotalGirth()); // 10 + 2 * 10 + 2 * (2 * (7 + 4) + 1) - first package is there twice!
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param float|null $weight
     * @param int|null $height
     * @param int|null $width
     * @param int|null $length
     * @return \App\Model\Product\Package\ProductPackage
     */
    private function createProductPackage(Product $product, ?float $weight, ?int $height, ?int $width, ?int $length): ProductPackage
    {
        $productPackage = new ProductPackage($product);
        $productPackage->setWeight($weight ?? 0.0);
        $productPackage->setHeight($height ?? 0);
        $productPackage->setWidth($width ?? 0);
        $productPackage->setLength($length ?? 0);

        return $productPackage;
    }

    /**
     * @param int $productId
     * @return \App\Model\Product\Product
     */
    private function createProduct(int $productId): Product
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->method('getId')->willReturn($productId);

        return $product;
    }
}

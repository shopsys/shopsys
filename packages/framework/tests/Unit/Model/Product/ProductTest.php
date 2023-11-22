<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\Exception\MainVariantCannotBeVariantException;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductIsAlreadyMainVariantException;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductIsAlreadyVariantException;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductIsNotVariantException;
use Shopsys\FrameworkBundle\Model\Product\Exception\VariantCanBeAddedOnlyToMainVariantException;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductTest extends TestCase
{
    public function testNoVariant(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $product = Product::create($productData);

        $this->assertFalse($product->isVariant());
        $this->assertFalse($product->isMainVariant());
    }

    public function testIsVariant(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $variant = Product::create($productData);
        Product::createMainVariant($productData, [$variant]);

        $this->assertTrue($variant->isVariant());
        $this->assertFalse($variant->isMainVariant());
    }

    public function testIsMainVariant(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);

        $this->assertFalse($mainVariant->isVariant());
        $this->assertTrue($mainVariant->isMainVariant());
    }

    public function testGetMainVariant(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);

        $this->assertSame($mainVariant, $variant->getMainVariant());
    }

    public function testGetMainVariantException(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $product = Product::create($productData);

        $this->expectException(ProductIsNotVariantException::class);
        $product->getMainVariant();
    }

    public function testCreateVariantFromVariantException(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $variant = Product::create($productData);
        $variant2 = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);
        Product::createMainVariant($productData, [$variant2]);

        $this->expectException(ProductIsAlreadyVariantException::class);
        $mainVariant->addVariant($variant2);
    }

    public function testCreateVariantFromMainVariantException(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $variant = Product::create($productData);
        $variant2 = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);
        $mainVariant2 = Product::createMainVariant($productData, [$variant2]);

        $this->expectException(MainVariantCannotBeVariantException::class);
        $mainVariant->addVariant($mainVariant2);
    }

    public function testCreateMainVariantFromVariantException(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $variant = Product::create($productData);
        $variant2 = Product::create($productData);
        $variant3 = Product::create($productData);
        Product::createMainVariant($productData, [$variant]);
        Product::createMainVariant($productData, [$variant2]);

        $this->expectException(VariantCanBeAddedOnlyToMainVariantException::class);
        $variant2->addVariant($variant3);
    }

    public function testAddSelfAsVariantException(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);

        $this->expectException(MainVariantCannotBeVariantException::class);
        $mainVariant->addVariant($mainVariant);
    }

    public function testMarkForVisibilityRecalculation(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $product = Product::create($productData);
        $product->markForVisibilityRecalculation();
        $this->assertTrue($product->isMarkedForVisibilityRecalculation());
    }

    public function testMarkForVisibilityRecalculationMainVariant(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);
        $mainVariant->markForVisibilityRecalculation();
        $this->assertTrue($mainVariant->isMarkedForVisibilityRecalculation());
        $this->assertTrue($variant->isMarkedForVisibilityRecalculation());
    }

    public function testMarkForVisibilityRecalculationVariant(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);
        $variant->markForVisibilityRecalculation();
        $this->assertTrue($variant->isMarkedForVisibilityRecalculation());
        $this->assertTrue($mainVariant->isMarkedForVisibilityRecalculation());
    }

    public function testDeleteResultNotVariant(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $product = Product::create($productData);

        $this->assertEmpty($product->getProductDeleteResult()->getProductsForRecalculations());
    }

    public function testDeleteResultVariant(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);

        $this->assertSame([$mainVariant], $variant->getProductDeleteResult()->getProductsForRecalculations());
    }

    public function testDeleteResultMainVariant(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);

        $this->assertEmpty($mainVariant->getProductDeleteResult()->getProductsForRecalculations());
        $this->assertFalse($variant->isVariant());
    }

    public function testCheckIsNotMainVariantException(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $variant = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant]);

        $this->expectException(ProductIsAlreadyMainVariantException::class);
        $mainVariant->checkIsNotMainVariant();
    }

    public function testRefreshVariants(): void
    {
        $productData = TestProductProvider::getTestProductData();

        $variant1 = Product::create($productData);
        $variant2 = Product::create($productData);
        $variant3 = Product::create($productData);
        $mainVariant = Product::createMainVariant($productData, [$variant1, $variant2]);

        $currentVariants = [$variant2, $variant3];
        $mainVariant->refreshVariants($currentVariants);

        $variantsArray = $mainVariant->getVariants();

        $this->assertNotContains($variant1, $variantsArray);
        $this->assertContains($variant2, $variantsArray);
        $this->assertContains($variant3, $variantsArray);
    }
}

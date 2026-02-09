<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Exception\MainVariantCannotBeVariantException;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductIsAlreadyVariantException;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductIsNotVariantException;
use Shopsys\FrameworkBundle\Model\Product\Exception\VariantCanBeAddedOnlyToMainVariantException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Tests\FrameworkBundle\Unit\TestCase;

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
        $this->setValueOfProtectedProperty($variant2, 'id', 2);
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
        $this->setValueOfProtectedProperty($mainVariant2, 'id', 2);

        $this->expectException(MainVariantCannotBeVariantException::class);
        $mainVariant->addVariant($mainVariant2);
    }

    public function testCreateMainVariantFromVariantException(): void
    {
        $productData = TestProductProvider::getTestProductData();
        $variant = Product::create($productData);
        $variant2 = Product::create($productData);
        $this->setValueOfProtectedProperty($variant2, 'id', 2);
        $variant3 = Product::create($productData);
        $this->setValueOfProtectedProperty($variant3, 'id', 3);
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
        $this->setValueOfProtectedProperty($mainVariant, 'id', 1);

        $this->expectException(MainVariantCannotBeVariantException::class);
        $mainVariant->addVariant($mainVariant);
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

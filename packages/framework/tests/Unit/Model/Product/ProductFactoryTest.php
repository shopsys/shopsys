<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFactory;

class ProductFactoryTest extends TestCase
{
    protected ProductFactory $productFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->productFactory = new ProductFactory(
            new EntityNameResolver([]),
        );

        parent::setUp();
    }

    public function testCreateVariant()
    {
        $mainProduct = Product::create(TestProductProvider::getTestProductData());
        $variants = [];

        $mainVariant = $this->productFactory->createMainVariant(TestProductProvider::getTestProductData(), $mainProduct, $variants);

        $this->assertNotSame($mainProduct, $mainVariant);
        $this->assertTrue(in_array($mainProduct, $mainVariant->getVariants(), true));
    }
}
